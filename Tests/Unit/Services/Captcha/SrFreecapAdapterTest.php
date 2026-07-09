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

namespace Evoweb\SfRegister\Tests\Unit\Services\Captcha;

use Evoweb\SfRegister\Services\Captcha\SrFreecapAdapter;
use Evoweb\SfRegister\Services\Session;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class SrFreecapAdapterTest extends UnitTestCase
{
    /**
     * A mocked Locales singleton is registered in isValidDelegatesToCaptchaServiceAndReturnsFalseWithErrorWhenCheckWordFails()
     * to make the production code's LocalizationUtility::translate() call (invoked from addError()) work without a full
     * DI container. It must be reset afterwards, hence this framework flag.
     */
    protected bool $resetSingletonInstances = true;

    protected Session&MockObject $session;

    public function setUp(): void
    {
        parent::setUp();

        $this->session = $this->createMock(Session::class);
    }

    /**
     * The production isValid() error path calls LocalizationUtility::translate(), which needs a
     * LanguageServiceFactory built via TYPO3's DI container. Plain unit tests have no container, so
     * GeneralUtility::makeInstance(LanguageServiceFactory::class) would otherwise throw an
     * ArgumentCountError. The collaborators are mocked instead so the real production code path can
     * run and be asserted on, per the "prefer mocking the collaborator so the test runs" guidance.
     */
    protected function mockLocalizationServiceToReturn(string $translation): void
    {
        $locales = $this->createMock(Locales::class);
        $locales->method('createLocaleFromRequest')->willReturn(new Locale('en'));
        GeneralUtility::setSingletonInstance(Locales::class, $locales);

        $languageService = $this->createMock(LanguageService::class);
        $languageService->method('translate')->willReturn($translation);

        $languageServiceFactory = $this->createMock(LanguageServiceFactory::class);
        $languageServiceFactory->method('create')->willReturn($languageService);
        GeneralUtility::addInstance(LanguageServiceFactory::class, $languageServiceFactory);
    }

    /**
     * Builds the subject through its real constructor. The sr_freecap extension is not
     * installed in this test environment, so ExtensionManagementUtility::isLoaded('sr_freecap')
     * is genuinely false here - this exercises the real, unmocked "extension not available"
     * branch of __construct().
     */
    protected function createSubject(): SrFreecapAdapter
    {
        return new SrFreecapAdapter($this->session);
    }

    /**
     * Because sr_freecap is not installed, the real constructor can never populate
     * captchaService with the SJBR\SrFreecap\PiBaseApi collaborator. To exercise isValid()'s
     * delegation logic, the constructor is bypassed and a test double is injected into the
     * captchaService property via reflection instead. captchaService is typed as a plain
     * `?object`, so any object exposing a compatible checkWord() method works as a stand-in.
     */
    protected function createSubjectWithCaptchaService(object $captchaService): SrFreecapAdapter
    {
        $reflectionClass = new \ReflectionClass(SrFreecapAdapter::class);
        $subject = $reflectionClass->newInstanceWithoutConstructor();

        $sessionProperty = $reflectionClass->getProperty('session');
        $sessionProperty->setAccessible(true);
        $sessionProperty->setValue($subject, $this->session);

        $captchaServiceProperty = $reflectionClass->getProperty('captchaService');
        $captchaServiceProperty->setAccessible(true);
        $captchaServiceProperty->setValue($subject, $captchaService);

        return $subject;
    }

    /**
     * Stand-in for SJBR\SrFreecap\PiBaseApi::checkWord(), tracking whether/with-what it was
     * called so delegation and pass-through can be asserted.
     */
    protected function createCaptchaServiceStub(bool $checkWordResult): CaptchaServiceStub
    {
        return new CaptchaServiceStub($checkWordResult);
    }

    // -- __construct ------------------------------------------------------------------------------

    #[Test]
    public function constructSetsCaptchaServiceToNullWhenSrFreecapExtensionIsNotLoaded(): void
    {
        $subject = $this->createSubject();

        $reflectionProperty = new \ReflectionProperty(SrFreecapAdapter::class, 'captchaService');
        $reflectionProperty->setAccessible(true);

        self::assertNull($reflectionProperty->getValue($subject));
    }

    // -- isValid ----------------------------------------------------------------------------------

    #[Test]
    public function isValidReturnsTrueWithoutTouchingSessionWhenCaptchaServiceIsNotAvailable(): void
    {
        $subject = $this->createSubject();

        $this->session->expects($this->never())->method('get');
        $this->session->expects($this->never())->method('set');

        self::assertTrue($subject->isValid('anything'));
    }

    #[Test]
    public function isValidSkipsDelegationAndReturnsTrueWhenSessionAlreadyMarksCaptchaAsValid(): void
    {
        // checkWordResult false would fail the test below if checkWord() were called at all.
        $captchaService = $this->createCaptchaServiceStub(false);
        $this->session->method('get')->with('captchaWasValid')->willReturn(true);
        $this->session->expects($this->never())->method('set');

        $subject = $this->createSubjectWithCaptchaService($captchaService);

        self::assertTrue($subject->isValid('word'));
        self::assertSame(0, $captchaService->checkWordCallCount);
    }

    #[Test]
    public function isValidDelegatesToCaptchaServiceAndReturnsTrueWhenCheckWordSucceeds(): void
    {
        $captchaService = $this->createCaptchaServiceStub(true);
        $this->session->method('get')->with('captchaWasValid')->willReturn(false);
        $this->session->expects($this->once())->method('set')->with('captchaWasValid', true);

        $subject = $this->createSubjectWithCaptchaService($captchaService);

        self::assertTrue($subject->isValid('correct-word'));
        self::assertSame(1, $captchaService->checkWordCallCount);
        self::assertSame('correct-word', $captchaService->receivedValue);
        self::assertSame([], $subject->getErrors());
    }

    #[Test]
    public function isValidDelegatesToCaptchaServiceAndReturnsFalseWithErrorWhenCheckWordFails(): void
    {
        $this->mockLocalizationServiceToReturn('Please enter the correct captcha word.');

        $captchaService = $this->createCaptchaServiceStub(false);
        $this->session->method('get')->with('captchaWasValid')->willReturn(false);
        $this->session->expects($this->once())->method('set')->with('captchaWasValid', false);

        $subject = $this->createSubjectWithCaptchaService($captchaService);

        self::assertFalse($subject->isValid('wrong-word'));
        self::assertSame(1, $captchaService->checkWordCallCount);
        self::assertSame('wrong-word', $captchaService->receivedValue);

        $errors = $subject->getErrors();
        self::assertCount(1, $errors);
        self::assertSame(1306910429, $errors[0]->getCode());
        self::assertSame('Please enter the correct captcha word.', $errors[0]->getMessage());
    }
}

/**
 * Stand-in for SJBR\SrFreecap\PiBaseApi (which does not exist in this test environment, see
 * SrFreecapAdapterTest::createCaptchaServiceStub()). Only exposes what SrFreecapAdapter::isValid()
 * actually calls, plus call tracking so delegation and pass-through can be asserted.
 */
class CaptchaServiceStub
{
    public int $checkWordCallCount = 0;

    public ?string $receivedValue = null;

    public function __construct(private readonly bool $checkWordResult) {}

    public function checkWord(string $value): bool
    {
        $this->checkWordCallCount++;
        $this->receivedValue = $value;

        return $this->checkWordResult;
    }
}
