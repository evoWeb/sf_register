<?php

namespace Evoweb\SfRegister\Tests\Functional\Controller;

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

use Evoweb\SfRegister\Controller\FeuserPasswordController;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\Session;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FeuserPasswordControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_users.csv');
    }

    ##[Test]
    public function userIsLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        $this->createEmptyFrontendUser();
        $this->request = $this->initializeTypoScriptFrontendController();

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '/');

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger(new NullLogger());
        $frontendUser->start($serverRequest);

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class, $frontendUser);

        $subject = new FeuserPasswordController(
            $context,
            $file,
            $userRepository,
            $userGroupRepository,
            $session
        );

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        self::assertFalse($method->invoke($subject));
    }

    ##[Test]
    public function userIsLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->createAndLoginFrontEndUser('2', [
            'password' => 'testOld',
            'comments' => ''
        ]);
        $this->request = $this->initializeTypoScriptFrontendController();

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '/');

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger(new NullLogger());
        $frontendUser->start($serverRequest);

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class, $frontendUser);

        $subject = new FeuserPasswordController(
            $context,
            $file,
            $userRepository,
            $userGroupRepository,
            $session
        );

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        self::assertTrue($method->invoke($subject));
    }

    ##[Test]
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository(): void
    {
        if (!defined('PASSWORD_ARGON2I')) {
            self::markTestSkipped('Due to missing Argon2 in travisci.');
        }

        $expected = 'myPassword';

        $userId = $this->createAndLoginFrontEndUser('2', [
            'username' => 'unittest',
            'password' => $expected,
            'comments' => ''
        ]);
        $this->request = $this->initializeTypoScriptFrontendController();
        $GLOBALS['TSFE'] = $this->typoScriptFrontendController;

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class, $this->frontendUser);

        // we need to clone to create the object, else the isClone parameter is not set and both object wont match
        $userMock = clone new FrontendUser();
        $userMock->setPassword($expected);

        /** @var FrontendUserRepository|MockObject $userRepositoryMock */
        $userRepositoryMock = $this->getMockBuilder(FrontendUserRepository::class)
            ->onlyMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $userRepositoryMock->expects(self::once())
            ->method('findByUid')
            ->with(self::equalTo($userId))
            ->willReturn($userMock);
        $userRepositoryMock->expects(self::once())
            ->method('update')
            ->with(self::equalTo($userMock));

        $subject = new FeuserPasswordController(
            $context,
            $file,
            $userRepositoryMock,
            $userGroupRepository,
            $session
        );

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, ['encryptPassword' => '']);

        $view = $this->getMockBuilder(StandaloneView::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $property = $this->getPrivateProperty($subject, 'view');
        $property->setValue($subject, $view);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $subject->injectEventDispatcher($eventDispatcher);

        /** @var Password $passwordMock */
        $passwordMock = GeneralUtility::makeInstance(Password::class);
        $passwordMock->_setProperty('password', $expected);
        $subject->saveAction($passwordMock);
    }
}
