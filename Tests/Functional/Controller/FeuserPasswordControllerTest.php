<?php
namespace Evoweb\SfRegister\Tests\Functional\Controller;

/*
 * This file is developed by evoweb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Evoweb\SfRegister\Controller\FeuserPasswordController;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class FeuserPasswordControllerTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    public function setUp()
    {
        $this->testExtensionsToLoad[] = 'typo3conf/ext/sf_register';

        parent::setUp();
        $this->importDataSet('../Tests/Functional/Fixtures/fe_groups.xml');
        $this->initializeTypoScriptFrontendController();
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertFalse($method->invoke($subject));
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->createAndLoginFrontEndUser($GLOBALS['TSFE'], '1', [
            'password' => 'testOld',
            'comments' => ''
        ]);

        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }

    /**
     * @test
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
    {
        /** @var FeuserPasswordController|AccessibleObjectInterface $subject */
        $subject = $this->getAccessibleMock(
            FeuserPasswordController::class,
            null
        );
        $expected = 'myPassword';

        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }

        $userId = $this->createAndLoginFrontEndUser($GLOBALS['TSFE'], '1', [
            'password' => $expected,
            'comments' => ''
        ]);

        // we need to clone the create object else the isClone param
        // is not set and the both object wont match
        $userMock = clone(new \Evoweb\SfRegister\Domain\Model\FrontendUser());
        $userMock->setPassword($expected);

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->getMockBuilder(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class)
            ->setMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $repositoryMock->expects($this->once())
            ->method('update')
            ->with($userMock);
        $subject->injectUserRepository($repositoryMock);

        /** @var \Evoweb\SfRegister\Domain\Model\Password|MockObject $passwordMock */
        $passwordMock = $this->getMockBuilder(\Evoweb\SfRegister\Domain\Model\Password::class)
            ->getMock();
        $passwordMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($expected));
        $subject->saveAction($passwordMock);
    }


    public function getPrivateMethod($object, $methodName)
    {
        $classReflection = new \ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);
        $methodReflection->setAccessible(true);
        return $methodReflection;
    }

    public function initializeTypoScriptFrontendController()
    {
        $typoScriptFrontendController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
            null,
            1,
            0
        );
        $typoScriptFrontendController->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Page\PageRepository::class
        );
        $typoScriptFrontendController->tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\TypoScript\TemplateService::class
        );
        $typoScriptFrontendController->fe_user = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class
        );
        $GLOBALS['TSFE'] = $typoScriptFrontendController;
    }

    public function createAndLoginFrontEndUser(
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontendController,
        $frontEndUserGroups = '',
        array $recordData = []
    ) {
        $frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);
        $this->loginFrontEndUser($frontendController, $frontEndUserUid);
    }

    public function createFrontEndUser(string $frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserGroupsWithoutSpaces = str_replace(' ', '', $frontEndUserGroups);
        if (!preg_match('/^(?:[1-9]+[0-9]*,?)+$/', $frontEndUserGroupsWithoutSpaces)) {
            throw new \InvalidArgumentException(
                $frontEndUserGroups . ' must contain a comma-separated list of UIDs. Each UID must be > 0.',
                1334439059
            );
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439065);
        }
        if (isset($recordData['usergroup'])) {
            throw new \InvalidArgumentException('The column "usergroup" must not be set in $recordData.', 1334439071);
        }
        $completeRecordData = $recordData;
        $completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

        return (int)$this->createRecord('fe_users', $completeRecordData);
    }

    public function createRecord(string $tableName, array $insertArray): string
    {
        /** @var Connection $connection */
        $connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName);
        $types = [];
        $tableDetails = $connection->getSchemaManager()->listTableDetails($tableName);
        foreach ($insertArray as $columnName => $columnValue) {
            $types[] = $tableDetails->getColumn($columnName)->getType()->getBindingType();
        }

        $connection->insert('fe_users', $insertArray, $types);
        return $connection->lastInsertId($tableName);
    }

    public function loginFrontEndUser(
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontendController,
        int $frontEndUserUid
    ) {
        if (!$frontendController) {
            throw new \Exception('Please create a front end before calling loginFrontEndUser.', 1334439483);
        }
        if ((int)$frontEndUserUid === 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1334439475);
        }

        // Instead of passing the actual user data to createUserSession, we
        // pass an empty array to improve performance (e.g. no session record
        // will be written to the database).
        /** @noinspection PhpInternalEntityUsedInspection */
        $frontendController->fe_user->user = $frontendController->fe_user->getRawUserByUid($frontEndUserUid);
        $frontendController->fe_user->fetchGroupData();
        $frontendController->loginUser = 1;
    }
}
