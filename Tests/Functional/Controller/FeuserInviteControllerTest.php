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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use EvowebTests\TestClasses\Controller\FeuserInviteController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserInviteControllerTest extends AbstractTestBase
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
    ): FeuserInviteController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Invite',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Invite');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserInvite');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserInviteController $subject */
        $subject = $this->get(FeuserInviteController::class);
        $subject->set('request', $request);
        $subject->set('settings', []);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    /**
     * Replaces the FrontendUserRepository DI binding with a stub whose findByUid() always
     * returns null, regardless of the requested uid. Used to reproduce the "logged in FE user
     * session pointing at a fe_users row that no longer resolves" situation (e.g. the record was
     * hidden/deleted after the session was established) without needing to fabricate that state
     * through the real frontend session/context machinery.
     */
    protected function mockRepositoryFindByUidReturnsNull(): void
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByUid'])
            ->getMock();
        $repository->method('findByUid')->willReturn(null);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);
    }

    /**
     * Replaces the MailService DI binding with a spy that records sendEmails()/sendInvitation()
     * calls instead of rendering real Fluid mail templates and dispatching a real mailer, and
     * returns the given users so the resulting view assignment can be asserted.
     */
    protected function mockMailServiceExpectations(
        FrontendUser $expectedUser,
        FrontendUser $userAfterEmails,
        FrontendUser $userAfterInvitation,
    ): void {
        /** @var MailService&MockObject $mailService */
        $mailService = $this->getMockBuilder(MailService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendEmails', 'sendInvitation'])
            ->getMock();
        $mailService->expects($this->once())
            ->method('sendEmails')
            ->with(
                self::anything(),
                self::isType('array'),
                self::identicalTo($expectedUser),
                self::equalTo('Invite'),
                self::equalTo('inviteAction')
            )
            ->willReturn($userAfterEmails);
        $mailService->expects($this->once())
            ->method('sendInvitation')
            ->with(
                self::anything(),
                self::isType('array'),
                self::identicalTo($userAfterEmails),
                self::equalTo('Invite'),
                self::equalTo('ToRegister')
            )
            ->willReturn($userAfterInvitation);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(MailService::class, $mailService);
    }

    /**
     * Replaces the SessionService DI binding with a spy so the "captchaWasValid" removal at the
     * end of inviteAction() can be asserted as a concrete observable effect.
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

    // -- formAction -----------------------------------------------------------------------------

    #[Test]
    public function formActionAssignsGivenUserToViewWhenUserIsProvided(): void
    {
        $subject = $this->getSubject('form');
        $user = new FrontendUser();

        $response = $subject->formAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionAssignsNewFrontendUserWhenNotLoggedInAndNoUserGiven(): void
    {
        $subject = $this->getSubject('form');

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertInstanceOf(FrontendUser::class, $view->variables['user']);
        /** @var FrontendUser $viewUser */
        $viewUser = $view->variables['user'];
        self::assertNull($viewUser->getUid());
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionAssignsLoggedInUserWhenLoggedInAndNoUserGiven(): void
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
        /** @var FrontendUser $viewUser */
        $viewUser = $view->variables['user'];
        self::assertSame($loggedInUser->getUid(), $viewUser->getUid());
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * Bug-Protokoll (RED-verified): pre-fix, when the FE session reports a logged-in user
     * (userIsLoggedIn() === true) but that user's fe_users row no longer resolves through the
     * repository (e.g. hidden/deleted after the session was established, so getLoggedInUser()
     * returns null), formAction() passes null into `new InviteFormEvent($user, $this->settings)`.
     * InviteFormEvent extends AbstractEventWithUserAndSettings, whose constructor has a
     * non-nullable `FrontendUser $user` parameter, so this is a reachable TypeError, not just
     * dead code.
     *
     * 30e771a (sibling branch) fixes this by changing the assignment to
     * `$this->frontendUserService->getLoggedInUser() ?? new FrontendUser()`, so a fresh
     * FrontendUser instance is used instead of null.
     *
     * RED evidence (assertions below un-skipped and run against the pre-fix code):
     *   1) FeuserInviteControllerTest::formActionThrowsWhenLoggedInUserRecordCannotBeResolved
     *   TypeError: Evoweb\SfRegister\Controller\Event\AbstractEventWithUserAndSettings::
     *   __construct(): Argument #1 ($user) must be of type
     *   Evoweb\SfRegister\Domain\Model\FrontendUser, null given, called in
     *   .../Classes/Controller/FeuserInviteController.php on line 61
     * Re-skipped after confirming the failure.
     */
    #[Test]
    public function formActionThrowsWhenLoggedInUserRecordCannotBeResolved(): void
    {
        // Characterizes df53334 behaviour (see doc comment above): formAction() passes null into the
        // non-nullable InviteFormEvent constructor -> uncaught TypeError. 30e771a changes this via
        // "getLoggedInUser() ?? new FrontendUser()" (behaviour change, not a pure type-fix), so this
        // test goes RED once 30e771a is cherry-picked -> revert that part in 30e771a; the real fix
        // belongs in a later step.
        $this->loginFrontendUser('testuser', 'TestPa$5');
        $this->mockRepositoryFindByUidReturnsNull();
        $subject = $this->getSubject('form');

        $this->expectException(\TypeError::class);

        $subject->formAction(null);
    }

    // -- inviteAction ---------------------------------------------------------------------------

    #[Test]
    public function inviteActionSendsEmailsAndInvitationThenAssignsResultingUserToView(): void
    {
        $user = new FrontendUser();
        $user->setEmail('invitee@example.com');
        $userAfterEmails = new FrontendUser();
        $userAfterEmails->setEmail('invitee@example.com');
        $userAfterInvitation = new FrontendUser();
        $userAfterInvitation->setEmail('invitee@example.com');

        $this->mockMailServiceExpectations($user, $userAfterEmails, $userAfterInvitation);
        $this->mockSessionServiceRemoveExpectation();
        $subject = $this->getSubject('invite');

        $response = $subject->inviteAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($userAfterInvitation, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }
}
