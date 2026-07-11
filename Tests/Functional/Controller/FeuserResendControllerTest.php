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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Model\Email;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use EvowebTests\TestClasses\Controller\FeuserResendController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserResendControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();
    }

    /**
     * configurationManager is a shared object and will be a constructor parameter of the
     * controller (@see Bootstrap::initializeConfiguration).
     *
     * @param array<string, mixed> $arguments
     */
    protected function getSubject(
        string $controllerActionName,
        array $arguments = [],
    ): FeuserResendController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Resend',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Resend');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserResend');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserResendController $subject */
        $subject = $this->get(FeuserResendController::class);
        $subject->set('request', $request);
        $subject->set('settings', []);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    /**
     * Replaces the MailService DI binding with a spy that records the sendEmails() call instead
     * of rendering real Fluid mail templates and dispatching a real mailer.
     */
    protected function mockMailServiceSendEmailsExpectation(
        FrontendUser $expectedUser,
        FrontendUser $userAfterEmails,
    ): void {
        /** @var MailService&MockObject $mailService */
        $mailService = $this->getMockBuilder(MailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendEmails'])
            ->getMock();
        $mailService->expects($this->once())
            ->method('sendEmails')
            ->with(
                self::anything(),
                self::isType('array'),
                self::identicalTo($expectedUser),
                self::equalTo('Resend'),
                self::equalTo('mailAction')
            )
            ->willReturn($userAfterEmails);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(MailService::class, $mailService);
    }

    /**
     * Replaces the MailService DI binding with a spy asserting sendEmails() is never called,
     * used to characterize the "no user found for the given email" branch.
     */
    protected function mockMailServiceSendEmailsNeverCalled(): void
    {
        /** @var MailService&MockObject $mailService */
        $mailService = $this->getMockBuilder(MailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendEmails'])
            ->getMock();
        $mailService->expects($this->never())->method('sendEmails');

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(MailService::class, $mailService);
    }

    /**
     * Replaces the SessionService DI binding with a spy so the "captchaWasValid" removal at the
     * end of mailAction() can be asserted as a concrete observable effect.
     */
    protected function mockSessionServiceRemoveExpectation(): void
    {
        /** @var SessionService&MockObject $sessionService */
        $sessionService = $this->getMockBuilder(SessionService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();
        $sessionService->expects($this->once())
            ->method('remove')
            ->with(self::equalTo('captchaWasValid'))
            ->willReturnSelf();

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(SessionService::class, $sessionService);
    }

    // -- formAction -------------------------------------------------------------------------

    #[Test]
    public function formActionAssignsGivenEmailToViewWhenEmailIsProvided(): void
    {
        $subject = $this->getSubject('form');
        $email = new Email();
        $email->setEmail('given@example.com');

        $response = $subject->formAction($email);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($email, $view->variables['email']);
        self::assertSame('given@example.com', $view->variables['email']->getEmail());
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionAssignsNewEmptyEmailWhenNotLoggedInAndNoEmailGiven(): void
    {
        $subject = $this->getSubject('form');

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertInstanceOf(Email::class, $view->variables['email']);
        /** @var Email $viewEmail */
        $viewEmail = $view->variables['email'];
        self::assertSame('', $viewEmail->getEmail());
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionAssignsLoggedInUsersEmailWhenLoggedInAndNoEmailGiven(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');
        $subject = $this->getSubject('form');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $loggedInUser */
        $loggedInUser = $userRepository->findByUid(1);

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertInstanceOf(Email::class, $view->variables['email']);
        /** @var Email $viewEmail */
        $viewEmail = $view->variables['email'];
        self::assertSame($loggedInUser->getEmail(), $viewEmail->getEmail());
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    // -- mailAction -------------------------------------------------------------------------

    #[Test]
    public function mailActionResendsMailToUserFoundByEmailAndRemovesCaptchaSession(): void
    {
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $userAfterEmails = new FrontendUser();

        // findByEmail() matches either the "email" or the "username" column; the fixture user
        // has no email set, so its username is used to resolve it here.
        $this->mockMailServiceSendEmailsExpectation($user, $userAfterEmails);
        $this->mockSessionServiceRemoveExpectation();
        $subject = $this->getSubject('mail');
        $email = new Email();
        $email->setEmail('testuser');

        $response = $subject->mailAction($email);

        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function mailActionDoesNotResendMailWhenNoUserIsFoundByEmailButStillRemovesCaptchaSession(): void
    {
        $this->mockMailServiceSendEmailsNeverCalled();
        $this->mockSessionServiceRemoveExpectation();
        $subject = $this->getSubject('mail');
        $email = new Email();
        $email->setEmail('unknown@example.com');

        $response = $subject->mailAction($email);

        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }
}
