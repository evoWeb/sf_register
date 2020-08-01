<?php

namespace Evoweb\SfRegister\Tests\Functional;

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

use Evoweb\SfRegister\Tests\Functional\SiteHandling\SiteBasedTestTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

abstract class FunctionalTestCase extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    use SiteBasedTestTrait;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/sf_register'];

    /**
     * @var array[]
     */
    protected const LANGUAGE_PRESETS = [
        'EN' => ['id' => 0, 'title' => 'English', 'locale' => 'en_US.UTF8'],
    ];

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $frontendUser;

    public function getPrivateMethod($object, $methodName): \ReflectionMethod
    {
        $classReflection = new \ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);
        $methodReflection->setAccessible(true);
        return $methodReflection;
    }

    public function getPrivateProperty($object, $propertyName): \ReflectionProperty
    {
        $classReflection = new \ReflectionClass($object);
        $propertyReflection = $classReflection->getProperty($propertyName);
        $propertyReflection->setAccessible(true);
        return $propertyReflection;
    }

    public function initializeTypoScriptFrontendController()
    {
        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, '/'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/en/'),
            ],
            [
                $this->buildErrorHandlingConfiguration('Fluid', [404])
            ]
        );
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/en/';
        $_GET['id'] = 1;
        GeneralUtility::flushInternalRuntimeCaches();
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier('test');

        $this->typoScriptFrontendController = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $site->getDefaultLanguage(),
            new PageArguments(1, '0', [])
        );
        $this->typoScriptFrontendController->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        $this->typoScriptFrontendController->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        $this->typoScriptFrontendController->fe_user = $this->frontendUser;
    }

    public function createAndLoginFrontEndUser(string $frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);
        $this->loginFrontEndUser($frontEndUserUid);
        return $frontEndUserUid;
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

    public function loginFrontEndUser(int $frontEndUserUid)
    {
        if ((int)$frontEndUserUid === 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1334439475);
        }

        $this->frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class
        );
        $this->frontendUser->user = $this->frontendUser->getRawUserByUid($frontEndUserUid);
        $this->frontendUser->fetchGroupData();

        /** @var \TYPO3\CMS\Core\Context\UserAspect $aspect */
        $aspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Context\UserAspect::class,
            $this->frontendUser
        );

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $context->setAspect('frontend.user', $aspect);
        $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }

    public function createEmptyFrontendUser()
    {
        $this->frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class
        );

        /** @var \TYPO3\CMS\Core\Context\UserAspect $aspect */
        $aspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Context\UserAspect::class,
            $this->frontendUser
        );

        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $context->setAspect('frontend.user', $aspect);
        $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }
}
