<?php

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\Mock\FeuserPasswordController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserPasswordControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->initializeTypoScriptFrontendController();
    }

    #[Test]
    public function userIsLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '/');

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger(new NullLogger());
        $frontendUser->start($serverRequest);

        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->createMock(FrontendUserRepository::class);

        $subject = new FrontendUserService($context, $frontendUserRepository);

        $this->assertFalse($subject->userIsLoggedIn());
    }

    #[Test]
    public function userIsLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->loginFrontEndUser(1);

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->createMock(FrontendUserRepository::class);

        $subject = new FrontendUserService($context, $frontendUserRepository);

        $this->assertTrue($subject->userIsLoggedIn());
    }

    #[Test]
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository(): void
    {
        if (!defined('PASSWORD_ARGON2I')) {
            $this->markTestSkipped('Due to missing Argon2 in travisci.');
        }

        $userId = 1;
        $this->loginFrontEndUser($userId);

        /** @var ModifyValidator $modifyValidator */
        $modifyValidator = $this->createMock(ModifyValidator::class);
        /** @var FileService $fileService */
        $fileService = $this->createMock(FileService::class);

        $expected = 'myPassword';
        // we need to clone to create the object, else the isClone parameter is not set and both object wont match
        $userMock = clone new FrontendUser();
        $userMock->setPassword($expected);

        /** @var FrontendUserRepository|MockObject $frontendUserRepository */
        $frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->onlyMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendUserRepository->expects($this->once())
            ->method('findByUid')
            ->with($this->equalTo($userId))
            ->willReturn($userMock);
        $frontendUserRepository->expects($this->once())
            ->method('update')
            ->with($this->equalTo($userMock));

        $context = GeneralUtility::makeInstance(Context::class);

        $frontendUserService = new FrontendUserService(
            $context,
            $frontendUserRepository
        );

        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $subject = new FeuserPasswordController(
            $modifyValidator,
            $fileService,
            $frontendUserRepository,
            $frontendUserService
        );

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, ['encryptPassword' => '']);

        $view = $this->getMockBuilder(StandaloneView::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $view->expects($this->once())
            ->method('render')
            ->willReturn('Password successfully updated');

        $property = $this->getPrivateProperty($subject, 'view');
        $property->setValue($subject, $view);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $subject->injectEventDispatcher($eventDispatcher);

        $this->request = $this->request->withAttribute('extbase', new ExtbaseRequestParameters());

        $request = new Request($this->request);
        $request = $request->withAttribute('currentContentObject', new ContentObjectRenderer());
        $subject->set('request', $request);

        /** @var Password $passwordMock */
        $passwordMock = GeneralUtility::makeInstance(Password::class);
        $passwordMock->_setProperty('password', $expected);
        $subject->saveAction($passwordMock);
    }
}
