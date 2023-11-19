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
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractTestBase extends FunctionalTestCase
{
    use SiteBasedTestTrait;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sf_register'
    ];

    /**
     * @var array[]
     */
    protected const LANGUAGE_PRESETS = [
        'EN' => [
            'id' => 0,
            'title' => 'English',
            'locale' => 'en_US.UTF8'
        ],
    ];

    protected ?TypoScriptFrontendController $typoScriptFrontendController = null;

    protected ?FrontendUserAuthentication $frontendUser = null;

    protected ServerRequest $request;

    public function initializeTypoScriptFrontendController(): ServerRequest
    {
        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => [
                    'EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript',
                    'EXT:sf_register/Configuration/TypoScript/minimal/constants.typoscript',
                ],
                'setup' => [
                    'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                    'EXT:sf_register/Configuration/TypoScript/minimal/setup.typoscript',
                    __DIR__ . '/../Fixtures/PageWithUserObjectUsingSlWithLLL.typoscript'
                ]
            ]
        );
        $this->writeSiteConfiguration(
            'website-local',
            $this->buildSiteConfiguration(1, 'http://localhost/'),
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

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withQueryParams($_GET);

        $context = GeneralUtility::makeInstance(Context::class);

        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier('website-local');
        $request->withAttribute('site', $site);

        $pageArguments = new PageArguments(1, '0', []);

        $controller = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $context,
            $site,
            $request->getAttribute('language', $site->getDefaultLanguage()),
            $pageArguments,
            $this->frontendUser
        );
        $controller->no_cache = true;
        $controller->determineId($request);
        $request = $request->withAttribute('frontend.controller', $controller);
        $request = $controller->getFromCache($request);

        return $request;
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
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName);
        $types = [];
        $tableDetails = $connection->getSchemaManager()->listTableDetails($tableName);
        foreach ($insertArray as $columnName => $columnValue) {
            $types[] = $tableDetails->getColumn($columnName)->getType()->getBindingType();
        }

        $connection->insert('fe_users', $insertArray, $types);
        return $connection->lastInsertId($tableName);
    }

    public function loginFrontEndUser(int $frontEndUserUid): void
    {
        if ($frontEndUserUid === 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1334439475);
        }

        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '/');

        $this->frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $this->frontendUser->setLogger(new NullLogger());
        $this->frontendUser->start($serverRequest);
        $this->frontendUser->user = $this->frontendUser->getRawUserByUid($frontEndUserUid);
        $this->frontendUser->unpack_uc();
        $this->frontendUser->fetchGroupData($serverRequest);

        $userAspect = $this->frontendUser->createUserAspect();

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('frontend.user', $userAspect);
    }

    public function createEmptyFrontendUser(): void
    {
        $this->frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);

        /** @var UserAspect $aspect */
        $aspect = GeneralUtility::makeInstance(UserAspect::class, $this->frontendUser);

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('frontend.user', $aspect);
        $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }

    public function getPrivateMethod($object, $methodName): \ReflectionMethod
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getMethod($methodName);
    }

    public function getPrivateProperty($object, $propertyName): \ReflectionProperty
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getProperty($propertyName);
    }
}
