<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Services;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Services\Event\NotifyAdminCreateSaveEvent;
use Evoweb\SfRegister\Services\Event\PreSubmitMailEvent;
use Evoweb\SfRegister\Services\Mail;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\FluidViewFactory;

class MailTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        // getSubject() resolves labels via LocalizationUtility::translate(), which needs
        // a resolvable language on the current global request.
        $language = new SiteLanguage(0, 'en_US.UTF-8', new Uri('https://typo3-testing.local/'), ['title' => 'English']);
        $this->request = $this->request->withAttribute('language', $language);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    /**
     * @param array<string, mixed> $frameworkConfigurationOverrides
     */
    protected function getSubject(
        array $frameworkConfigurationOverrides = [],
        ?EventDispatcherInterface $eventDispatcher = null,
        ?MailerInterface $mailer = null
    ): Mail {
        $frameworkConfiguration = array_replace_recursive(
            [
                'extensionName' => 'SfRegister',
                'pluginName' => 'Create',
                'view' => [
                    'templateRootPaths' => ['EXT:sf_register/Resources/Private/Templates/'],
                    'partialRootPaths' => ['EXT:sf_register/Resources/Private/Partials/'],
                    'layoutRootPaths' => ['EXT:sf_register/Resources/Private/Layouts/'],
                ],
            ],
            $frameworkConfigurationOverrides
        );

        $configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $configurationManager->method('getConfiguration')->willReturn($frameworkConfiguration);

        /** @var FluidViewFactory $viewFactory */
        $viewFactory = $this->get(FluidViewFactory::class);

        return new Mail(
            $eventDispatcher ?? $this->createMock(EventDispatcherInterface::class),
            $configurationManager,
            $viewFactory,
            $mailer ?? $this->createMock(MailerInterface::class)
        );
    }

    protected function createExtbaseRequest(string $controllerName = 'FeuserCreate'): Request
    {
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName($controllerName);
        $extbaseAttribute->setControllerActionName('create');

        return new Request($this->request->withAttribute('extbase', $extbaseAttribute));
    }

    protected function createUser(string $username = 'tester'): FrontendUser
    {
        $user = new FrontendUser();
        $user->setUsername($username);
        $user->setEmail($username . '@example.com');
        return $user;
    }

    /**
     * @return array<string, string>
     */
    protected function getUserRecipient(Mail $subject, FrontendUser $user): array
    {
        $method = $this->getPrivateMethod($subject, 'getUserRecipient');
        /** @var array<string, string> $result */
        $result = $method->invoke($subject, $user);
        return $result;
    }

    /**
     * @return array<string, array{0: array<string, array<string, string>>, 1: bool}>
     */
    public static function isNotifyAdminDataProvider(): array
    {
        return [
            'enabled setting returns true' => [['notifyAdmin' => ['createSave' => '1']], true],
            'disabled setting returns false' => [['notifyAdmin' => ['createSave' => '0']], false],
            'missing type returns false' => [['notifyAdmin' => ['createConfirm' => '1']], false],
            'missing notifyAdmin key returns false' => [[], false],
        ];
    }

    /**
     * @param array<string, array<string, string>> $settings
     */
    #[DataProvider('isNotifyAdminDataProvider')]
    #[Test]
    public function isNotifyAdminRespectsSettings(array $settings, bool $expected): void
    {
        $subject = $this->getSubject();

        self::assertSame($expected, $subject->isNotifyAdmin($settings, 'CreateSave'));
    }

    /**
     * @return array<string, array{0: array<string, array<string, string>>, 1: bool}>
     */
    public static function isNotifyUserDataProvider(): array
    {
        return [
            'enabled setting returns true' => [['notifyUser' => ['createSave' => '1']], true],
            'disabled setting returns false' => [['notifyUser' => ['createSave' => '0']], false],
            'missing type returns false' => [['notifyUser' => ['createConfirm' => '1']], false],
            'missing notifyUser key returns false' => [[], false],
        ];
    }

    /**
     * @param array<string, array<string, string>> $settings
     */
    #[DataProvider('isNotifyUserDataProvider')]
    #[Test]
    public function isNotifyUserRespectsSettings(array $settings, bool $expected): void
    {
        $subject = $this->getSubject();

        self::assertSame($expected, $subject->isNotifyUser($settings, 'CreateSave'));
    }

    #[Test]
    public function getSubjectReturnsTranslatedSubjectWithPlaceholdersResolved(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSubject');
        $user = $this->createUser('tester');
        $settings = ['sitename' => 'Test Site'];

        $result = $method->invoke($subject, $settings, 'NotifyAdminCreateSave', $user);

        self::assertSame('User tester registered on site Test Site', $result);
    }

    #[Test]
    public function getUserRecipientUsesFullNameWhenFirstAndLastNameAreSet(): void
    {
        $subject = $this->getSubject();
        $user = $this->createUser('tester');
        $user->setFirstName('  Jane ');
        $user->setLastName('Doe ');
        $user->setEmail(' jane.doe@example.com ');

        $recipient = $this->getUserRecipient($subject, $user);

        self::assertSame(['jane.doe@example.com' => 'Jane  Doe'], $recipient);
    }

    #[Test]
    public function getUserRecipientFallsBackToUsernameWithoutFirstOrLastName(): void
    {
        $subject = $this->getSubject();
        $user = $this->createUser(' tester ');
        $user->setEmail('tester@example.com');

        $recipient = $this->getUserRecipient($subject, $user);

        self::assertSame(['tester@example.com' => 'tester'], $recipient);
    }

    #[Test]
    public function getViewReturnsFluidViewInstance(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getView');
        $request = $this->createExtbaseRequest();

        $result = $method->invoke($subject, $request, 'FeuserCreate', 'form', 'html');

        self::assertInstanceOf(ViewInterface::class, $result);
    }

    #[Test]
    public function dispatchMailEventDispatchesPreSubmitMailEventAndReturnsMail(): void
    {
        $dispatchedEvents = [];
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PreSubmitMailEvent::class))
            ->willReturnCallback(function (object $event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $event;
                return $event;
            });

        $subject = $this->getSubject([], $eventDispatcher);
        $method = $this->getPrivateMethod($subject, 'dispatchMailEvent');
        $user = $this->createUser();
        $mail = new MailMessage();

        $result = $method->invoke($subject, ['fromEmail' => 'from@example.com'], $mail, $user);

        self::assertSame($mail, $result);
        self::assertCount(1, $dispatchedEvents);
        /** @var PreSubmitMailEvent $dispatchedEvent */
        $dispatchedEvent = $dispatchedEvents[0];
        self::assertSame($mail, $dispatchedEvent->getMail());
    }

    #[Test]
    public function dispatchUserEventDispatchesNamedEventAndReturnsUser(): void
    {
        $dispatchedEvents = [];
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(NotifyAdminCreateSaveEvent::class))
            ->willReturnCallback(function (object $event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $event;
                return $event;
            });

        $subject = $this->getSubject([], $eventDispatcher);
        $method = $this->getPrivateMethod($subject, 'dispatchUserEvent');
        $user = $this->createUser();
        $settings = ['sitename' => 'Test Site'];

        $result = $method->invoke($subject, $settings, 'NotifyAdminCreateSave', $user);

        self::assertSame($user, $result);
        self::assertCount(1, $dispatchedEvents);
    }

    /**
     * @param \Symfony\Component\Mime\Address[] $addresses
     * @return array<string, string>
     */
    protected function addressesToArray(array $addresses): array
    {
        $result = [];
        foreach ($addresses as $address) {
            $result[$address->getAddress()] = $address->getName();
        }
        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    protected function createMailSettings(): array
    {
        return [
            'sitename' => 'Test Site',
            'adminEmail' => [
                'fromEmail' => 'admin-from@example.com',
                'fromName' => 'Admin From',
                'toEmail' => 'admin-to@example.com',
                'toName' => 'Admin To',
                'replyEmail' => '',
                'replyName' => '',
            ],
            'userEmail' => [
                'fromEmail' => 'noreply@example.com',
                'fromName' => 'NoReply',
                'replyEmail' => '',
                'replyName' => '',
            ],
        ];
    }

    #[Test]
    public function sendNotifyAdminSendsMailToAdminAndDispatchesEvents(): void
    {
        $dispatchedEvents = [];
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(function (object $event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
            return $event;
        });

        $sentMails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with(self::isInstanceOf(MailMessage::class))
            ->willReturnCallback(function (MailMessage $mail) use (&$sentMails): void {
                $sentMails[] = $mail;
            });

        $subject = $this->getSubject([], $eventDispatcher, $mailer);
        $request = $this->createExtbaseRequest();
        $user = $this->createUser('tester');
        $settings = $this->createMailSettings();

        // @phpstan-ignore argument.type (createMailSettings() mirrors real TypoScript settings incl. scalar sitename)
        $result = $subject->sendNotifyAdmin($request, $settings, $user, 'Create', 'Save');

        self::assertSame($user, $result);
        self::assertCount(1, $sentMails);
        /** @var MailMessage $sentMail */
        $sentMail = $sentMails[0];
        self::assertSame(['admin-to@example.com' => 'Admin To'], $this->addressesToArray($sentMail->getTo()));
        self::assertSame('User tester registered on site Test Site', $sentMail->getSubject());
        self::assertStringContainsString('The user registration was saved', (string)$sentMail->getHtmlBody());

        self::assertCount(2, $dispatchedEvents);
        self::assertInstanceOf(PreSubmitMailEvent::class, $dispatchedEvents[0]);
        self::assertInstanceOf(NotifyAdminCreateSaveEvent::class, $dispatchedEvents[1]);
    }

    #[Test]
    public function sendNotifyUserSendsMailToUserRecipient(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static fn(object $event) => $event);

        $sentMails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (MailMessage $mail) use (&$sentMails): void {
                $sentMails[] = $mail;
            });

        $subject = $this->getSubject([], $eventDispatcher, $mailer);
        $request = $this->createExtbaseRequest();
        $user = $this->createUser('tester');
        $settings = $this->createMailSettings();

        // @phpstan-ignore argument.type (createMailSettings() mirrors real TypoScript settings incl. scalar sitename)
        $subject->sendNotifyUser($request, $settings, $user, 'Create', 'Save');

        self::assertCount(1, $sentMails);
        /** @var MailMessage $sentMail */
        $sentMail = $sentMails[0];
        self::assertSame(['tester@example.com' => 'tester'], $this->addressesToArray($sentMail->getTo()));
        self::assertSame('You registered yourself at Test Site as tester', $sentMail->getSubject());
    }

    #[Test]
    public function sendEmailsSendsToAdminAndUserWhenBothConfigured(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static fn(object $event) => $event);

        $sentMails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(function (MailMessage $mail) use (&$sentMails): void {
            $sentMails[] = $mail;
        });

        $subject = $this->getSubject([], $eventDispatcher, $mailer);
        $request = $this->createExtbaseRequest();
        $user = $this->createUser('tester');
        $settings = $this->createMailSettings();
        $settings['notifyAdmin'] = ['createSave' => '1'];
        $settings['notifyUser'] = ['createSave' => '1'];

        // @phpstan-ignore argument.type (createMailSettings() mirrors real TypoScript settings incl. scalar sitename)
        $result = $subject->sendEmails($request, $settings, $user, 'Create', 'saveAction');

        self::assertSame($user, $result);
        self::assertCount(2, $sentMails);
    }

    #[Test]
    public function sendEmailsSendsNoMailWhenNotConfigured(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $subject = $this->getSubject([], $eventDispatcher, $mailer);
        $request = $this->createExtbaseRequest();
        $user = $this->createUser('tester');
        $settings = $this->createMailSettings();

        // @phpstan-ignore argument.type (createMailSettings() mirrors real TypoScript settings incl. scalar sitename)
        $result = $subject->sendEmails($request, $settings, $user, 'Create', 'saveAction');

        self::assertSame($user, $result);
    }
}
