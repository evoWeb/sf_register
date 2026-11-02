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
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use EvowebTests\TestClasses\Controller\FeuserEditController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserEditControllerTest extends AbstractTestBase
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
        ?Request $originalRequest = null,
    ): FeuserEditController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Edit',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Edit');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserEdit');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }
        if ($originalRequest !== null) {
            $extbaseAttribute->setOriginalRequest($originalRequest);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserEditController $subject */
        $subject = $this->get(FeuserEditController::class);
        $subject->set('request', $request);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    /**
     * Builds an extbase Request usable as "original request" (e.g. the request that was
     * forwarded from), carrying its own arguments.
     *
     * @param array<string, mixed> $arguments
     */
    protected function buildOriginalRequest(string $controllerActionName, array $arguments = []): Request
    {
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Edit');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserEdit');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        return new Request($this->request->withAttribute('extbase', $extbaseAttribute));
    }

    /**
     * Replaces the FrontendUserRepository DI binding with a spy that records the update() call
     * instead of writing to the database. The real request pipeline flushes update()s to the
     * database via an unconditional persistAll() at the end of the request; since these tests
     * call the action method directly (bypassing that pipeline), asserting the update() call
     * itself is the reliable way to observe the repository interaction.
     *
     * findByUidIgnoringDisabledField() is stubbed as well: the actions resolve their user
     * through FrontendUserService::determineFrontendUser(), which shares this DI binding and
     * looks the user up by the uid in the request once the link hash validates.
     */
    protected function mockRepositoryUpdateExpectation(FrontendUser $expectedUser): void
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'findByUidIgnoringDisabledField'])
            ->getMock();
        $repository->expects($this->once())
            ->method('update')
            ->with(self::identicalTo($expectedUser));
        $repository->method('findByUidIgnoringDisabledField')
            ->with($expectedUser->getUid())
            ->willReturn($expectedUser);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);
    }

    /**
     * Replaces the uriBuilder of the shared (singleton) FrontendUserService with a test double
     * that skips real frontend link resolution (which would require a full site/TSFE setup
     * unrelated to the logic under test) while still returning a deterministic URI, so
     * redirectToPage()-based branches can be exercised without standing up a site.
     */
    protected function mockUriBuilderForRedirects(): void
    {
        $uriBuilder = $this->createMock(UriBuilder::class);
        $uriBuilder->method('reset')->willReturnSelf();
        $uriBuilder->method('setRequest')->willReturnSelf();
        $uriBuilder->method('setTargetPageUid')->willReturnSelf();
        $uriBuilder->method('setLinkAccessRestrictedPages')->willReturnSelf();
        $uriBuilder->method('setCreateAbsoluteUri')->willReturnSelf();
        $uriBuilder->method('setArguments')->willReturnSelf();
        $uriBuilder->method('build')->willReturn('https://typo3-testing.local/redirect-target');

        /** @var FrontendUserService $frontendUserService */
        $frontendUserService = $this->get(FrontendUserService::class);
        $this->getPrivateProperty($frontendUserService, 'uriBuilder')->setValue($frontendUserService, $uriBuilder);
    }

    // -- formAction ---------------------------------------------------------------------------

    #[Test]
    public function formActionAssignsGivenUserToView(): void
    {
        $subject = $this->getSubject('form');
        $user = new FrontendUser();

        $response = $subject->formAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertArrayNotHasKey('temporaryImage', $view->variables);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionForwardsTemporaryImageFromOriginalRequestWhenPresent(): void
    {
        $originalRequest = $this->buildOriginalRequest('preview', ['temporaryImage' => 'fileadmin/tmp/image.jpg']);
        $subject = $this->getSubject('form', [], $originalRequest);
        $user = new FrontendUser();

        $subject->formAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame('fileadmin/tmp/image.jpg', $view->variables['temporaryImage']);
    }

    // -- previewAction ------------------------------------------------------------------------

    #[Test]
    public function previewActionAssignsUserAndTemporaryImageToView(): void
    {
        $subject = $this->getSubject('preview', ['temporaryImage' => 'fileadmin/tmp/image.jpg']);
        $user = new FrontendUser();

        $response = $subject->previewAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertSame('fileadmin/tmp/image.jpg', $view->variables['temporaryImage']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function previewActionDoesNotAssignTemporaryImageWhenAbsentFromRequest(): void
    {
        $subject = $this->getSubject('preview');
        $user = new FrontendUser();

        $subject->previewAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertArrayNotHasKey('temporaryImage', $view->variables);
    }

    // -- saveAction -----------------------------------------------------------------------------

    #[Test]
    public function saveActionUpdatesUserInRepositoryAndAssignsItToViewByDefault(): void
    {
        $subject = $this->getSubject('save');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setFirstName('Jane');

        $response = $subject->saveAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());

        $row = $this->getConnectionPool()->getQueryBuilderForTable('fe_users')
            ->select('first_name')
            ->from('fe_users')
            ->where('uid = 1')
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame('Jane', $row['first_name']);
    }

    #[Test]
    public function saveActionForwardsToFormWhenForwardToEditAfterSaveIsEnabled(): void
    {
        $subject = $this->getSubject('save');
        $subject->set('settings', ['forwardToEditAfterSave' => true]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);

        $response = $subject->saveAction($user);

        self::assertInstanceOf(ForwardResponse::class, $response);
        self::assertSame('form', $response->getActionName());
    }

    // -- confirmAction --------------------------------------------------------------------------

    #[Test]
    public function confirmActionAssignsUserNotFoundWhenUserCannotBeDetermined(): void
    {
        $subject = $this->getSubject('confirm');

        $response = $subject->confirmAction(null, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    #[Test]
    public function confirmActionConfirmsPendingEmailChangeAndUpdatesUser(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryUpdateExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);
        $user->setDisable(false);
        $user->setEmail('old@example.com');
        $user->setEmailNew('new@example.com');

        $this->mockRepositoryUpdateExpectation($user);
        $subject = $this->getSubject('confirm', ['action' => 'confirm', 'user' => 1]);

        $response = $subject->confirmAction($user, $this->createLinkHash('confirm', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userConfirmed']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);

        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame('', $user->getEmailNew());
    }

    #[Test]
    public function confirmActionAssignsUserAlreadyConfirmedWhenNoEmailChangeIsPending(): void
    {
        $subject = $this->getSubject('confirm', ['action' => 'confirm', 'user' => 1]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(false);
        $user->setEmailNew('');

        $subject->confirmAction($user, $this->createLinkHash('confirm', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAlreadyConfirmed']);
    }

    #[Test]
    public function confirmActionAssignsUserNotConfirmedWhenUserIsDisabled(): void
    {
        $subject = $this->getSubject('confirm', ['action' => 'confirm', 'user' => 1]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(true);

        $subject->confirmAction($user, $this->createLinkHash('confirm', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotConfirmed']);
    }

    #[Test]
    public function confirmActionRedirectsToConfiguredPageWhenRedirectPostActivationPageIdIsSet(): void
    {
        $this->mockUriBuilderForRedirects();

        $subject = $this->getSubject('confirm', ['action' => 'confirm', 'user' => 1]);
        $subject->set('settings', ['redirectPostActivationPageId' => 2]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(false);
        $user->setEmailNew('new@example.com');

        $response = $subject->confirmAction($user, $this->createLinkHash('confirm', 1));

        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    // -- acceptAction ---------------------------------------------------------------------------

    #[Test]
    public function acceptActionAssignsUserNotFoundWhenUserCannotBeDetermined(): void
    {
        $subject = $this->getSubject('accept');

        $response = $subject->acceptAction(null, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    #[Test]
    public function acceptActionAcceptsDisabledUserAndUpdatesUser(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryUpdateExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);
        $user->setDisable(true);
        $user->setEmail('old@example.com');
        $user->setEmailNew('new@example.com');

        $this->mockRepositoryUpdateExpectation($user);
        $subject = $this->getSubject('accept', ['action' => 'accept', 'user' => 1]);

        $response = $subject->acceptAction($user, $this->createLinkHash('accept', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['adminAccept']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);

        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame('', $user->getEmailNew());
    }

    #[Test]
    public function acceptActionAssignsUserAlreadyConfirmedWhenUserIsNotDisabled(): void
    {
        $subject = $this->getSubject('accept', ['action' => 'accept', 'user' => 1]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(false);

        $subject->acceptAction($user, $this->createLinkHash('accept', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAlreadyConfirmed']);
    }

    #[Test]
    public function acceptActionRedirectsToConfiguredPageWhenRedirectPostActivationPageIdIsSet(): void
    {
        $this->mockUriBuilderForRedirects();

        $subject = $this->getSubject('accept', ['action' => 'accept', 'user' => 1]);
        $subject->set('settings', ['redirectPostActivationPageId' => 2]);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(true);

        $response = $subject->acceptAction($user, $this->createLinkHash('accept', 1));

        self::assertInstanceOf(RedirectResponse::class, $response);
    }
}
