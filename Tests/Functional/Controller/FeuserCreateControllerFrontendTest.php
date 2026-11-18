<?php

declare(strict_types=1);

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

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use EvowebTests\TestClasses\Validation\Validator\FailingValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Extbase\Security\HashScope;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

class FeuserCreateControllerFrontendTest extends AbstractTestBase
{
    protected const PLUGIN_NAMESPACE = 'tx_sfregister_create';

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->update('pages', ['slug' => '/', 'is_siteroot' => 1], ['uid' => 1]);
        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:sf_register/Configuration/TypoScript/minimal/setup.typoscript',
                'EXT:sf_register/Tests/Fixtures/CreatePlugin.typoscript',
            ],
            [
                'constants' => '@import \'EXT:sf_register/Configuration/TypoScript/Common/constants.typoscript\'',
            ]
        );
    }

    /**
     * Submits the preview form like a browser would. The save action fails validation
     * and the form needs to be shown with the submitted values and the error, instead
     * of forwarding back to the preview, which validates again and loops infinitely.
     */
    #[Test]
    public function failedSaveDisplaysFormWithSubmittedValues(): void
    {
        $user = [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane.doe@example.org',
            'privacy' => '1',
        ];

        $request = (new InternalRequest('https://example.org/'))->withMethod('POST');
        // nested body gets parsed by the testing framework, as guzzle is not able to build it from parsed body
        // action and controller are part of the body to not need a cHash
        $request->getBody()->write(http_build_query([
            self::PLUGIN_NAMESPACE => [
                'action' => 'proxy',
                'controller' => 'FeuserCreate',
                '__referrer' => $this->getReferrerOfPreview(['user' => $user]),
                'user' => $user,
                'save' => 'Save',
            ],
        ]));

        $response = $this->executeFrontendSubRequest($request);

        $body = (string)$response->getBody();

        // rendered form instead of preview or exception
        self::assertStringContainsString('id="sfrForm"', $body);
        self::assertMatchesRegularExpression('/<input[^>]+name="tx_sfregister_create\[user\]\[firstName\]"[^>]+value="Jane"/', $body);
        self::assertMatchesRegularExpression('/<input[^>]+name="tx_sfregister_create\[user\]\[lastName\]"[^>]+value="Doe"/', $body);
        self::assertMatchesRegularExpression('/<input[^>]+name="tx_sfregister_create\[user\]\[email\]"[^>]+value="jane\.doe@example\.org"/', $body);
        self::assertMatchesRegularExpression('/<input[^>]+name="tx_sfregister_create\[user\]\[privacy\]"[^>]+checked="checked"/', $body);
        self::assertStringContainsString(FailingValidator::MESSAGE, $body);
    }

    /**
     * The form maps an identity to an existing record. Without the uid validator of
     * the form, the data of any user could be rendered in the registration form.
     */
    #[Test]
    public function formDoesNotRenderDataOfExistingUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');
        $this->getConnectionPool()
            ->getConnectionForTable('fe_users')
            ->update(
                'fe_users',
                ['first_name' => 'Foreign', 'email' => 'foreign.user@example.org'],
                ['uid' => 1]
            );

        $request = (new InternalRequest('https://example.org/'))->withMethod('POST');
        $request->getBody()->write(http_build_query([
            self::PLUGIN_NAMESPACE => [
                'action' => 'form',
                'controller' => 'FeuserCreate',
                'user' => ['__identity' => 1],
            ],
        ]));

        $response = $this->executeFrontendSubRequest($request);

        $body = (string)$response->getBody();

        self::assertStringNotContainsString('foreign.user@example.org', $body);
        self::assertStringNotContainsString('Foreign', $body);
    }

    /**
     * Hidden __referrer fields like the FormViewHelper renders them in the preview
     *
     * @param array<string, mixed> $previewArguments arguments the preview action was called with
     * @return array<string, string>
     */
    protected function getReferrerOfPreview(array $previewArguments): array
    {
        /** @var HashService $hashService */
        $hashService = $this->get(HashService::class);

        return [
            '@extension' => 'SfRegister',
            '@controller' => 'FeuserCreate',
            '@action' => 'preview',
            'arguments' => $hashService->appendHmac(
                base64_encode(serialize($previewArguments)),
                HashScope::ReferringArguments->prefix()
            ),
            '@request' => $hashService->appendHmac(
                (string)json_encode([
                    '@extension' => 'SfRegister',
                    '@controller' => 'FeuserCreate',
                    '@action' => 'preview',
                ]),
                HashScope::ReferringRequest->prefix()
            ),
        ];
    }
}
