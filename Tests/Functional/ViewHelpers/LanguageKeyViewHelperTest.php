<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\ViewHelpers\LanguageKeyViewHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * LanguageKeyViewHelper::render() outputs a language "key" derived from the current
 * request's site language, optionally gated on whether a static_* table has a matching
 * per-language column:
 *
 *   $languageCode = $this->getLanguageCode();
 *   $type = $this->getConfiguredType();
 *   if ($languageCode !== '' && $type !== '') {
 *       if ($type == 'zones') {
 *           $languageCode = $this->hasTableColumn('static_country_zones', 'zn_name_' . $languageCode) ? $languageCode : '';
 *       } elseif ($type == 'languages') {
 *           $languageCode = $this->hasTableColumn('static_languages', 'lg_name_' . $languageCode) ? $languageCode : '';
 *       }
 *   }
 *   return ucfirst(strtolower($languageCode) ?: 'en');
 *
 * getLanguageCode() (frontend branch) reads the SiteLanguage attached to the request's
 * "language" attribute and returns its ISO language code (Locale::getLanguageCode()).
 *
 * git show 30e771a (phpstan fix) on this class - what ACTUALLY changed, vs. the task
 * brief's claim that getLanguageCode/hasTableColumn/render all changed:
 *
 * - getLanguageCode(): DID change. Pre-fix reads $this->getRequest(), a removed helper
 *   that returned $GLOBALS['TYPO3_REQUEST'] directly. Post-fix reads the request from
 *   $this->renderingContext->getAttribute(ServerRequestInterface::class) instead. For
 *   any request driven through this test's renderTemplate() helper, both sources are
 *   populated with an equivalent request (this test mirrors the frontend "language"
 *   attribute onto $GLOBALS['TYPO3_REQUEST'], which is exactly what the pre-fix
 *   getRequest() reads), so no observable output difference is reachable from these
 *   tests - it is a request-plumbing refactor, not a behavior change, for the scenarios
 *   in scope here (single current-request rendering).
 *   Also in getLanguageCode(): the backend fallback branch
 *   (`$this->getBackendUserAuthentication()->uc['lang']`) gained a null-safe `?->` in
 *   30e771a (paired with a defensive instanceof check added to
 *   getBackendUserAuthentication() itself, see below). This branch is only reached when
 *   ApplicationType::fromRequest()->isFrontend() is false. It is out of scope for this
 *   task (which is specifically about "the language code from the request's site
 *   language", i.e. the frontend branch) and is not exercised here; all tests keep the
 *   request's applicationType attribute at REQUESTTYPE_FE (frontend) as set up by
 *   AbstractTestBase::createServerRequest().
 * - getConfiguredType(): DID change (not listed in the brief at all) - gained
 *   `$type = is_string($type) ? $type : '';`. This is pure phpstan type-narrowing: the
 *   `type` argument is registered as `'string'` via registerArgument(), so Fluid's
 *   argument validation already guarantees $this->arguments['type'] is a string (or
 *   absent) before render() ever runs it through getConfiguredType(). Dead code for any
 *   real invocation - no behavior divergence, no skip needed. Exercised implicitly by
 *   every render() call below.
 * - getBackendUserAuthentication(): DID change - added an `instanceof BackendUserAuthentication`
 *   guard so it returns null instead of a non-instance value. Only observable from the
 *   backend branch of getLanguageCode() (see above) - out of scope here, not exercised.
 * - hasTableColumn(): UNCHANGED by 30e771a (confirmed via `git show 30e771a`). The brief
 *   is wrong to list it as a changed method. Tested below directly via reflection (true
 *   for an existing static_languages column, false for a bogus one) and indirectly
 *   through render()'s branching.
 * - render(): UNCHANGED by 30e771a. Also mislisted by the brief. Tested below for its
 *   exact output across the 'languages'/'zones'/no-type argument combinations.
 *
 * Net result: the only reachable, in-scope 30e771a change is the getRequest() ->
 * renderingContext plumbing swap inside getLanguageCode(), which produces no observable
 * difference for the frontend site-language scenarios under test - no Bug-Protokoll or
 * Deprecation-Protokoll skip is needed.
 */
class LanguageKeyViewHelperTest extends AbstractTestBase
{
    /**
     * Redeclaring the property REPLACES the parent array, so the parent's entries are
     * copied verbatim and the static_info_tables stub (owning the static_languages /
     * static_country_zones tables inspected by hasTableColumn()) is appended.
     *
     * @var array<non-empty-string>
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sf_register',
        'typo3conf/ext/sf_register/Tests/Fixtures/Extensions/test_classes',
        'typo3conf/ext/sf_register/Tests/Fixtures/Extensions/static_info_tables',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );
    }

    protected function buildSiteLanguage(string $locale): SiteLanguage
    {
        return new SiteLanguage(0, $locale, new Uri('https://example.org/'), ['title' => $locale]);
    }

    /**
     * Attaches the given SiteLanguage to the request's "language" attribute on BOTH
     * $this->request and $GLOBALS['TYPO3_REQUEST'], since the pre-fix getLanguageCode()
     * reads the language exclusively from the latter (via the removed getRequest()
     * helper).
     */
    protected function setRequestLanguage(SiteLanguage $language): void
    {
        $this->request = $this->request->withAttribute('language', $language);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    /**
     * @param array<string, mixed> $variables
     */
    protected function renderTemplate(string $template, array $variables = []): string
    {
        $this->request = $this->request
            ->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
        $extbaseRequest = new ExtbaseRequest($this->request);
        $extbaseRequest = $extbaseRequest
            ->withAttribute('currentContentObject', $this->get(ContentObjectRenderer::class));

        $renderingContextFactory = $this->get(RenderingContextFactory::class);
        self::assertInstanceOf(RenderingContextFactory::class, $renderingContextFactory);
        $context = $renderingContextFactory->create();
        $context->setAttribute(ServerRequestInterface::class, $extbaseRequest);
        $context->getTemplatePaths()->setTemplateSource(
            '{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template
        );
        foreach ($variables as $name => $value) {
            $context->getVariableProvider()->add($name, $value);
        }
        $actual = (new TemplateView($context))->render();
        self::assertIsString($actual);
        return $actual;
    }

    /**
     * getLanguageCode(): with no `type` argument, getConfiguredType() returns '', so
     * render() never calls hasTableColumn() and outputs the site language's ISO code
     * unfiltered (ucfirst(strtolower($languageCode))) - proving getLanguageCode() picks
     * up the actual site language of the request, and that it varies across languages.
     */
    #[Test]
    #[DataProvider('siteLanguageProvider')]
    public function rendersTheIsoCodeOfTheRequestSiteLanguageWhenNoTypeIsGiven(string $locale, string $expected): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage($locale));

        self::assertSame($expected, $this->renderTemplate('<register:languageKey />'));
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function siteLanguageProvider(): iterable
    {
        yield 'default language (English)' => ['en_US.UTF-8', 'En'];
        yield 'non-default language (German)' => ['de_DE.UTF-8', 'De'];
        yield 'non-default language (French)' => ['fr_FR.UTF-8', 'Fr'];
    }

    /**
     * getLanguageCode(): when the request carries no "language" attribute at all (not a
     * SiteLanguage instance), languageCode stays '' and render() falls back to the
     * documented default 'en'.
     */
    #[Test]
    public function rendersTheDefaultEnglishLanguageKeyWhenNoSiteLanguageIsPresentOnTheRequest(): void
    {
        self::assertSame('En', $this->renderTemplate('<register:languageKey />'));
    }

    /**
     * hasTableColumn(): asserted directly via reflection since it is protected and not
     * otherwise reachable from outside render(). True for a column that really exists
     * on the fixture static_languages table (see Tests/Fixtures/Extensions/
     * static_info_tables/ext_tables.sql), false for a bogus column name on the same
     * table.
     */
    #[Test]
    public function hasTableColumnReturnsTrueForAnExistingColumnAndFalseForABogusColumn(): void
    {
        $subject = $this->get(LanguageKeyViewHelper::class);
        self::assertInstanceOf(LanguageKeyViewHelper::class, $subject);
        $method = $this->getPrivateMethod($subject, 'hasTableColumn');

        self::assertTrue($method->invoke($subject, 'static_languages', 'lg_name_en'));
        self::assertFalse($method->invoke($subject, 'static_languages', 'lg_name_bogus'));
    }

    /**
     * render() + type="languages": hasTableColumn('static_languages', 'lg_name_en')
     * is true (the fixture table has that column), so the language code is kept as-is.
     */
    #[Test]
    public function renderKeepsTheLanguageCodeWhenTypeIsLanguagesAndTheColumnExists(): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage('en_US.UTF-8'));

        self::assertSame('En', $this->renderTemplate('<register:languageKey type="languages" />'));
    }

    /**
     * render() + type="languages": hasTableColumn('static_languages', 'lg_name_de')
     * is false (the fixture table only defines lg_name_en), so render() resets the
     * language code and falls back to 'en'. Contrasted with
     * rendersTheIsoCodeOfTheRequestSiteLanguageWhenNoTypeIsGiven() above - which renders
     * 'De' for the very same German site language when no `type` is given - this proves
     * the 'En' fallback here is caused by hasTableColumn() returning false, not by
     * getLanguageCode() itself.
     */
    #[Test]
    public function renderFallsBackToEnglishWhenTypeIsLanguagesAndTheColumnIsMissing(): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage('de_DE.UTF-8'));

        self::assertSame('En', $this->renderTemplate('<register:languageKey type="languages" />'));
    }

    /**
     * render() + type="zones": hasTableColumn('static_country_zones', 'zn_name_en')
     * is true (the fixture table has that column), covering the sibling `zones` branch
     * of render() as well.
     */
    #[Test]
    public function renderKeepsTheLanguageCodeWhenTypeIsZonesAndTheColumnExists(): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage('en_US.UTF-8'));

        self::assertSame('En', $this->renderTemplate('<register:languageKey type="zones" />'));
    }

    /**
     * render() + type="zones": hasTableColumn('static_country_zones', 'zn_name_de')
     * is false (the fixture table only defines zn_name_en/zn_name_local), so render()
     * falls back to 'en' just like the 'languages' branch above.
     */
    #[Test]
    public function renderFallsBackToEnglishWhenTypeIsZonesAndTheColumnIsMissing(): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage('de_DE.UTF-8'));

        self::assertSame('En', $this->renderTemplate('<register:languageKey type="zones" />'));
    }

    /**
     * getConfiguredType(): an unsupported `type` value behaves the same as no `type` at
     * all - hasTableColumn() is never consulted and the site language's code passes
     * through unfiltered.
     */
    #[Test]
    public function rendersTheLanguageCodeUnfilteredForAnUnsupportedType(): void
    {
        $this->setRequestLanguage($this->buildSiteLanguage('de_DE.UTF-8'));

        self::assertSame('De', $this->renderTemplate('<register:languageKey type="unsupported" />'));
    }
}
