<?php
namespace Evoweb\SfRegister\Tests\Functional;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

abstract class FunctionalTestCase extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
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
