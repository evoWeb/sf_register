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

use Evoweb\SfRegister\Controller\FeuserPasswordController as FeuserPasswordController;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\Session;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FeuserPasswordControllerTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp(): void
    {
        defined('LF') ?: define('LF', chr(10));
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');
    }

    /**
     * @test
     */
    public function userIsLoggedInReturnsFalseIfNotLoggedIn()
    {
        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        /** @var Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $logger = new NullLogger();

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger($logger);
        $frontendUser->start();

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

    /**
     * @test
     */
    public function userIsLoggedInReturnsTrueIfLoggedIn()
    {
        $this->createAndLoginFrontEndUser('2', [
            'password' => 'testOld',
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        /** @var Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $logger = new NullLogger();

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger($logger);
        $frontendUser->start();

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

    /**
     * @test
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
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
        $this->initializeTypoScriptFrontendController();
        $GLOBALS['TSFE'] = $this->typoScriptFrontendController;

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class, $this->frontendUser);

        // we need to clone the create object, else the isClone parameter is not set and both object wont match
        $userMock = clone new \Evoweb\SfRegister\Domain\Model\FrontendUser();
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

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject->injectObjectManager($objectManager);

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, ['encryptPassword' => '']);

        $view = $this->getMockBuilder(StandaloneView::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $property = $this->getPrivateProperty($subject, 'view');
        $property->setValue($subject, $view);

        /** @var \TYPO3\CMS\Core\EventDispatcher\EventDispatcher $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Core\EventDispatcher\EventDispatcher::class);
        $subject->injectEventDispatcher($eventDispatcher);

        /** @var \Evoweb\SfRegister\Domain\Model\Password $passwordMock */
        $passwordMock = GeneralUtility::makeInstance(\Evoweb\SfRegister\Domain\Model\Password::class);
        $passwordMock->_setProperty('password', $expected);
        $subject->saveAction($passwordMock);
    }
}
