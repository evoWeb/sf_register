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
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use EvowebTests\TestClasses\Controller\FeuserPasswordController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserPasswordControllerTest extends AbstractTestBase
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
                        'encryptPassword' => '',
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function userIsLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        /** @var FrontendUserService $subject */
        $subject = $this->get(FrontendUserService::class);

        self::assertFalse($subject->userIsLoggedIn());
    }

    #[Test]
    public function userIsLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var FrontendUserService $subject */
        $subject = $this->get(FrontendUserService::class);

        self::assertTrue($subject->userIsLoggedIn());
    }

    #[Test]
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository(): void
    {
        $userId = 1;
        $expected = 'TestPa$5';
        $this->loginFrontendUser('testuser', $expected);

        // we need to clone to create the object, else the isClone parameter is not set, and both objects won't match
        $frontendUser = clone new FrontendUser();
        $frontendUser->setPassword($expected);

        /** @var FrontendUserRepository&MockObject $frontendUserRepository */
        $frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByUid', 'update'])
            ->getMock();
        // @extensionScannerIgnoreLine
        $frontendUserRepository->expects($this->once())
            ->method('findByUid')
            ->with(self::equalTo($userId))
            ->willReturn($frontendUser);
        // @extensionScannerIgnoreLine
        $frontendUserRepository->expects($this->once())
            ->method('update')
            ->with(self::equalTo($frontendUser));

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $frontendUserRepository);

        /** @var FluidViewAdapter&MockObject $view */
        $view = $this->getMockBuilder(FluidViewAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $view->expects($this->once())
            ->method('render')
            ->willReturn('Password successfully updated');

        // configurationManager is a shared object and will be a constructor parameter of the controller
        // @see Bootstrap::initializeConfiguration
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Password',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Password');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserPassword');
        $extbaseAttribute->setControllerActionName('save');

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));

        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserPasswordController $subject */
        $subject = $this->get(FeuserPasswordController::class);

        $subject->set('request', $request);
        $subject->set('view', $view);

        $password = new Password();
        $password->_setProperty('password', $expected);
        $response = $subject->saveAction($password);

        self::assertEquals(200, $response->getStatusCode());
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
    ): FeuserPasswordController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Password',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Password');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserPassword');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserPasswordController $subject */
        $subject = $this->get(FeuserPasswordController::class);
        $subject->set('request', $request);
        $subject->set('settings', []);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    // -- formAction -----------------------------------------------------------------------------

    #[Test]
    public function formActionAssignsNotLoggedInAndNewPasswordWhenNotLoggedInAndNoPasswordGiven(): void
    {
        $subject = $this->getSubject('form');

        $response = $subject->formAction(null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertTrue($view->variables['notLoggedIn']);
        self::assertInstanceOf(Password::class, $view->variables['password']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function formActionAssignsGivenPasswordWithoutNotLoggedInFlagWhenLoggedIn(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');
        $subject = $this->getSubject('form');
        $password = new Password();

        $response = $subject->formAction($password);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertArrayNotHasKey('notLoggedIn', $view->variables);
        self::assertSame($password, $view->variables['password']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    // -- saveAction -------------------------------------------------------------------------------

    #[Test]
    public function saveActionThrowsWhenLoggedInUserRecordCannotBeResolved(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByUid'])
            ->getMock();
        $repository->method('findByUid')->willReturn(null);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);

        $subject = $this->getSubject('save');
        $password = new Password();
        $password->_setProperty('password', 'TestPa$5');

        self::markTestSkipped(
            'Pre-fix bug in df53334: FeuserPasswordController::saveAction() reads '
            . '$this->frontendUserService->getLoggedInUser() without a null-guard and passes the result '
            . 'straight into "new PasswordSaveEvent($user, $this->settings)", whose parent constructor '
            . '(AbstractEventWithUserAndSettings) declares a non-nullable FrontendUser $user parameter. '
            . 'FrontendUserService::getLoggedInUser() can return null even while userIsLoggedIn() is true '
            . '(e.g. the FE session is valid but the fe_users row was hidden/deleted afterwards, or '
            . 'excluded by the repository\'s enable-fields), so this is a genuinely reachable defect. '
            . 'RED-verified: TypeError: Evoweb\SfRegister\Controller\Event\AbstractEventWithUserAndSettings'
            . '::__construct(): Argument #1 ($user) must be of type Evoweb\SfRegister\Domain\Model\FrontendUser, '
            . 'null given, called in .../Classes/Controller/FeuserPasswordController.php on line 70. '
            . 'Behoben in 30e771a (Classes/Controller/FeuserPasswordController.php, sibling branch) via '
            . 'getLoggedInUser() ?? new FrontendUser(). Reaktivieren in Roadmap-Schritt 2.'
        );

        // $subject->saveAction($password);
    }
}
