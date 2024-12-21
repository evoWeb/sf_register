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

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EqualCurrentPasswordValidatorTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_users.csv');

        $this->initializeRequest();
        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'badWordList' => 'god, sex, password',
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function isUserLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        $this->assertFalse((bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn'));
    }

    #[Test]
    public function isValidReturnsTrueIfLoggedIn(): void
    {
        $expected = 'TestPa$5';
        $this->loginFrontendUser('testuser', $expected);

        /** @var EqualCurrentPasswordValidator $subject */
        $subject = $this->get(EqualCurrentPasswordValidator::class);
        $this->assertFalse($subject->validate($expected)->hasErrors());
    }
}
