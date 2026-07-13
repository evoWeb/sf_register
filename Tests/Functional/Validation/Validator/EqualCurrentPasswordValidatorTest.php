<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EqualCurrentPasswordValidatorTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_users.csv');

        $this->createServerRequest();
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

        self::assertFalse((bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn'));
    }

    #[Test]
    public function isValidReturnsTrueIfLoggedIn(): void
    {
        $expected = 'TestPa$5';
        $this->loginFrontendUser('testuser', $expected);

        /** @var EqualCurrentPasswordValidator $subject */
        $subject = $this->get(EqualCurrentPasswordValidator::class);
        self::assertFalse($subject->validate($expected)->hasErrors());
    }

    #[Test]
    public function isValidReturnsFalseIfPasswordDoesNotMatchCurrentPassword(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        $this->request = $this->request->withAttribute('language', (new NullSite())->getDefaultLanguage());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        /** @var EqualCurrentPasswordValidator $subject */
        $subject = $this->get(EqualCurrentPasswordValidator::class);
        $result = $subject->validate('someOtherPassword');

        self::assertTrue($result->hasErrors());
        self::assertSame(1301599507, $result->getErrors()[0]->getCode());
    }

    /**
     * Pre-fix bug in df53334: EqualCurrentPasswordValidator::isValid() reads
     * $this->frontendUserService->getLoggedInUser() without a null-guard and calls
     * $user->getPassword() directly. FrontendUserService::getLoggedInUser() can return null even
     * while userIsLoggedIn() is true (e.g. the FE session is valid but the fe_users row was
     * hidden/deleted afterwards, or excluded by the repository's enable-fields), so this is a
     * genuinely reachable defect - same root cause as FeuserPasswordControllerTest::
     * saveActionThrowsWhenLoggedInUserRecordCannotBeResolved(). RED-verified: un-skipping this
     * test and calling $subject->validate() throws "Error: Call to a member function
     * getPassword() on null" from EqualCurrentPasswordValidator::isValid(); catch (Exception
     * $exception) does not catch this \Error, so it propagates uncaught. Behoben in 30e771a
     * (Classes/Validation/Validator/EqualCurrentPasswordValidator.php, sibling branch) via
     * $user?->getPassword() ?? ''. Reaktivieren in Roadmap-Schritt 2.
     */
    #[Test]
    public function isValidReportsErrorWhenLoggedInUserRecordCannotBeResolved(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByUid'])
            ->getMock();
        $repository->method('findByUid')->willReturn(null);

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(FrontendUserRepository::class, $repository);

        // When getLoggedInUser() returns null, isValid() uses $user?->getPassword() ?? '' instead of
        // dereferencing null, so validation reports an error gracefully instead of raising an Error.
        /** @var EqualCurrentPasswordValidator $subject */
        $subject = $this->get(EqualCurrentPasswordValidator::class);

        $result = $subject->validate('TestPa$5');

        self::assertTrue($result->hasErrors());
    }
}
