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

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * Sends the confirm and refuse requests of TYPO3-EXT-SA-2026-038 through the whole frontend
 * stack (middlewares, Extbase argument mapping, persistence) and asserts on the database row of
 * the targeted frontend user. The requests are POST requests because the arguments of a POST body
 * are not covered by the cHash of the page.
 *
 * The create controller declares the hash as a required argument, so an attacker has to send a
 * wrong or empty value instead of leaving it out.
 */
class FeuserCreateControllerRequestTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Request/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Request/fe_users.csv');

        $this->setUpFrontendRootPage(
            1,
            [
                'constants' => [
                    'EXT:sf_register/Configuration/TypoScript/minimal/constants.typoscript',
                ],
                'setup' => [
                    'EXT:sf_register/Tests/Fixtures/Request/setup.typoscript',
                ],
            ]
        );
        $this->writeSiteConfiguration(
            'website-local',
            $this->buildSiteConfiguration(1, 'http://localhost/'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
            ]
        );
    }

    /**
     * @param array<string, string> $arguments
     */
    protected function sendPluginRequest(array $arguments): void
    {
        $request = (new InternalRequest('http://localhost/'))
            ->withPageId(1)
            ->withMethod('POST')
            ->withParsedBody(['tx_sfregister_create' => $arguments]);

        $this->executeFrontendSubRequest($request);
    }

    /**
     * Reads the row of the fixture user with a plain query. The query builder based helpers hide
     * disabled and deleted records, which are exactly the states that are asserted on here.
     *
     * @return array{disable: int, deleted: int, activated_on: int}
     */
    protected function fetchVictim(): array
    {
        $row = $this->getConnectionPool()
            ->getConnectionForTable('fe_users')
            ->executeQuery('SELECT disable, deleted, activated_on FROM fe_users WHERE uid = 1')
            ->fetchAssociative();

        self::assertIsArray($row, 'the frontend user of the fixture must exist as a row');

        return [
            'disable' => (int)$row['disable'],
            'deleted' => (int)$row['deleted'],
            'activated_on' => (int)$row['activated_on'],
        ];
    }

    /**
     * The state of the fixture user before any request. The user is active, because the
     * advisory describes requests against active frontend users. Extbase does not map a
     * disabled user from the user argument at all.
     *
     * @return array{disable: int, deleted: int, activated_on: int}
     */
    protected function untouchedVictim(): array
    {
        return ['disable' => 0, 'deleted' => 0, 'activated_on' => 0];
    }

    #[Test]
    public function fixtureUserIsActiveAndNotDeleted(): void
    {
        // Baseline that the other tests compare against.
        self::assertSame($this->untouchedVictim(), $this->fetchVictim());
    }

    #[Test]
    public function refuseRequestWithValidHashDeletesTheUser(): void
    {
        // Positive control, shows that the request reaches the action and persists its effect.
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'refuse',
            'user' => '1',
            'hash' => GeneralUtility::hmac('refuse::1'),
        ]);

        self::assertSame(1, $this->fetchVictim()['deleted']);
    }

    #[Test]
    public function refuseRequestWithWrongHashKeepsTheUser(): void
    {
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'refuse',
            'user' => '1',
            'hash' => 'wrong-hash',
        ]);

        self::assertSame($this->untouchedVictim(), $this->fetchVictim());
    }

    #[Test]
    public function declineRequestWithValidHashDeletesTheUser(): void
    {
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'decline',
            'user' => '1',
            'hash' => GeneralUtility::hmac('decline::1'),
        ]);

        self::assertSame(1, $this->fetchVictim()['deleted']);
    }

    #[Test]
    public function declineRequestWithWrongHashKeepsTheUser(): void
    {
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'decline',
            'user' => '1',
            'hash' => 'wrong-hash',
        ]);

        self::assertSame($this->untouchedVictim(), $this->fetchVictim());
    }

    #[Test]
    public function confirmRequestWithValidHashMarksTheUserAsActivated(): void
    {
        // Positive control for the path that writes the user instead of deleting it.
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'confirm',
            'user' => '1',
            'hash' => GeneralUtility::hmac('confirm::1'),
        ]);

        self::assertGreaterThan(0, $this->fetchVictim()['activated_on']);
    }

    #[Test]
    public function confirmRequestWithWrongHashLeavesTheUserUntouched(): void
    {
        $this->sendPluginRequest([
            'controller' => 'FeuserCreate',
            'action' => 'confirm',
            'user' => '1',
            'hash' => 'wrong-hash',
        ]);

        self::assertSame($this->untouchedVictim(), $this->fetchVictim());
    }
}
