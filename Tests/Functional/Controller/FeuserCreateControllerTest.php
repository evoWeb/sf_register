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
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use Evoweb\SfRegister\Validation\Validator\UniqueValidator;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserCreateControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'fields' => [
                            'selected' => 'username',
                        ],
                        'validation.' => [
                            'create.' => [
                                'username.' => [
                                    1 => '"' . RequiredValidator::class . '"',
                                    2 => '"' . StringLengthValidator::class . '",options={"minimum":4,"maximum":80}',
                                    3 => '"' . UniqueValidator::class . '"',
                                    4 => '"' . UniqueValidator::class . '", options={"global": 1}',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function isUserValidatorSet(): void
    {
        // configurationManager is a shared object and will be a constructor parameter of the controller
        // @see Bootstrap::initializeConfiguration
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName('create');

        $extbaseAttribute->setArgument('user', [
            'gender' => 1,
            'title' => 'none',
            'firstName' => '',
            'lastName' => '',
            'username' => '',
            'passwort' => '',
            'passwortRepeat' => '',
            'email' => '',
            'emailRepeat' => '',
            'gtc' => 1,
            'privacy' => 1,
        ]);

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));

        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserCreateController $subject */
        $subject = $this->get(FeuserCreateController::class);

        $subject->set('request', $request);
        $subject->set('actionMethodName', 'formAction');
        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        $validator = $arguments->getArgument('user')->getValidator();

        self::assertInstanceOf(UserValidator::class, $validator);
    }

    /**
     * configurationManager is a shared object and will be a constructor parameter of the
     * controller (@see Bootstrap::initializeConfiguration).
     *
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $settings
     */
    protected function getSubject(
        string $controllerActionName,
        array $arguments = [],
        array $settings = [],
    ): FeuserCreateController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserCreateController $subject */
        $subject = $this->get(FeuserCreateController::class);
        $subject->set('request', $request);
        $subject->set('settings', $settings);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    /**
     * Replaces the FrontendUserRepository DI binding with a spy that records the update() call
     * instead of writing to the database. confirmAction()/acceptAction() never call persistAll()
     * after update() (that normally happens once at the end of a full Extbase request, which
     * these tests bypass by calling the action method directly), so asserting the update() call
     * itself is the reliable way to observe the repository interaction.
     */
    protected function mockRepositoryUpdateExpectation(FrontendUser $expectedUser): void
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update'])
            ->getMock();
        $repository->expects($this->once())
            ->method('update')
            ->with(self::identicalTo($expectedUser));

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);
    }

    /**
     * Replaces the FrontendUserRepository DI binding with a spy that records the remove() call
     * instead of writing to the database. refuseAction()/declineAction() never call persistAll()
     * after remove() (same reasoning as mockRepositoryUpdateExpectation() above), so asserting
     * the remove() call itself is the reliable way to observe the repository interaction.
     */
    protected function mockRepositoryRemoveExpectation(FrontendUser $expectedUser): void
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();
        $repository->expects($this->once())
            ->method('remove')
            ->with(self::identicalTo($expectedUser));

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);
    }

    // -- formAction / setupCheck ----------------------------------------------------------------

    #[Test]
    public function formActionAssignsGivenUserToViewWhenSetupCheckPasses(): void
    {
        // 'username' selected + useEmailAddressAsUsername not set keeps all three default setup
        // checks (UserGroupCheck, AutologinCheck, UsernameCheck) from triggering.
        $subject = $this->getSubject('form', [], ['fields' => ['selected' => ['username']]]);
        $user = new FrontendUser();

        $response = $subject->formAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionRendersWithoutAssigningUserWhenNoUserIsGiven(): void
    {
        $subject = $this->getSubject('form', [], ['fields' => ['selected' => ['username']]]);

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertArrayNotHasKey('user', $view->variables);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionReturnsSetupResponseWhenSetupCheckFails(): void
    {
        // useEmailAddressAsUsername=true together with 'username' still selected as a field
        // triggers Services/Setup/UsernameCheck, which formAction()'s setupCheck() call returns
        // as an early HtmlResponse before any view rendering happens.
        $subject = $this->getSubject('form', [], [
            'useEmailAddressAsUsername' => true,
            'fields' => ['selected' => ['username']],
        ]);

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame([], $view->variables);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertStringContainsString('Please check your setup.', $response->getBody()->getContents());
    }

    // -- previewAction ---------------------------------------------------------------------------

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

    // -- saveAction -----------------------------------------------------------------------------

    #[Test]
    public function saveActionPersistsNewUserAndAssignsItToView(): void
    {
        $subject = $this->getSubject('save');
        $user = new FrontendUser();
        $user->setUsername('newlycreated');
        $user->setPassword('TestPa$5');

        $response = $subject->saveAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $row = $queryBuilder
            ->select('uid', 'username')
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter('newlycreated')))
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($row);
        self::assertSame('newlycreated', $row['username']);
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
    public function confirmActionActivatesUserAndUpdatesRepository(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryUpdateExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);
        $user->setDisable(true);

        $this->mockRepositoryUpdateExpectation($user);
        $subject = $this->getSubject('confirm');

        $response = $subject->confirmAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userConfirmed']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);

        self::assertInstanceOf(\DateTime::class, $user->getActivatedOn());
        self::assertFalse($user->getDisable());
    }

    #[Test]
    public function confirmActionAssignsUserAlreadyConfirmedWhenAlreadyActivated(): void
    {
        $subject = $this->getSubject('confirm');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setActivatedOn(new \DateTime('now'));

        $subject->confirmAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAlreadyConfirmed']);
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
    public function acceptActionAcceptsDisabledUserAndUpdatesRepository(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryUpdateExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);
        $user->setDisable(true);

        $this->mockRepositoryUpdateExpectation($user);
        $subject = $this->getSubject('accept');

        $response = $subject->acceptAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAccepted']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);

        self::assertFalse($user->getDisable());
        self::assertInstanceOf(\DateTime::class, $user->getActivatedOn());
    }

    #[Test]
    public function acceptActionAssignsUserAlreadyAcceptedWhenUserIsNotDisabled(): void
    {
        $subject = $this->getSubject('accept');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        $user->setDisable(false);

        $subject->acceptAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAlreadyAccepted']);
    }

    // -- refuseAction ---------------------------------------------------------------------------

    #[Test]
    public function refuseActionAssignsUserNotFoundWhenUserCannotBeDetermined(): void
    {
        $subject = $this->getSubject('refuse');

        $response = $subject->refuseAction(null, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    #[Test]
    public function refuseActionRemovesUserFromRepositoryAndAssignsUserRefused(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryRemoveExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);

        $this->mockRepositoryRemoveExpectation($user);
        $subject = $this->getSubject('refuse');

        $response = $subject->refuseAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userRefused']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    // -- declineAction --------------------------------------------------------------------------

    #[Test]
    public function declineActionAssignsUserNotFoundWhenUserCannotBeDetermined(): void
    {
        $subject = $this->getSubject('decline');

        $response = $subject->declineAction(null, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    #[Test]
    public function declineActionRemovesUserFromRepositoryAndAssignsUserDeclined(): void
    {
        // Built manually (instead of fetched via the container-shared FrontendUserRepository)
        // because mockRepositoryRemoveExpectation() below replaces that very DI binding, which
        // Symfony refuses once the original service has already been resolved once.
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);

        $this->mockRepositoryRemoveExpectation($user);
        $subject = $this->getSubject('decline');

        $response = $subject->declineAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userDeclined']);
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
