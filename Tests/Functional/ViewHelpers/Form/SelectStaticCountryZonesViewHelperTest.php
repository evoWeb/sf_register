<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * SelectStaticCountryZonesViewHelper::initialize() builds the `options` array handed to
 * the inherited AbstractSelectViewHelper::render() from the `static_country_zones` rows
 * matching the given `parent` (a country ISO-2 code) via
 * StaticCountryZoneRepository::findAllByIso2()
 * (`SELECT * FROM static_country_zones WHERE zn_country_iso_2 = :iso ORDER BY zn_name_local`).
 * Each row keeps its default optionValueField ("uid") and optionLabelField
 * ("zn_name_local").
 *
 *   if ($this->arguments['parent'] === null || !ExtensionManagementUtility::isLoaded('static_info_tables')) {
 *       return;
 *   }
 *
 * initialize() only fills `options` when BOTH a non-null `parent` argument is given AND
 * the `static_info_tables` extension (which owns the `static_country_zones` table) is
 * loaded; otherwise it returns early and `options` stays unset, so render() falls back to
 * an empty select.
 *
 * `ExtensionManagementUtility::isLoaded('static_info_tables')` checks a declared TYPO3
 * extension-KEY, not the sjbr/static-info-tables composer package. This test therefore
 * loads a minimal stub extension declaring exactly that extension-key plus the
 * `static_country_zones` table schema
 * (Tests/Fixtures/Extensions/static_info_tables/), which makes isLoaded() true and lets
 * the fixture rows populate the real repository query. That covers the positive path
 * (options built + zn_country_iso_2 filtering) and the `$parent === null` early-return
 * clause for real, without needing the actual sjbr vendor package installed.
 */
class SelectStaticCountryZonesViewHelperTest extends AbstractTestBase
{
    /**
     * Redeclaring the property REPLACES the parent array, so the parent's entries are
     * copied verbatim and the static_info_tables stub is appended.
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
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/static_country_zones.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );
    }

    /**
     * @param array<string, mixed> $variables
     */
    protected function renderTemplate(string $template, array $variables = []): string
    {
        $this->request = $this->request
            ->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
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
     * Positive path: with static_info_tables loaded and a parent iso given, initialize()
     * builds one option per matching zone (value = uid, label = zn_name_local), ordered by
     * zn_name_local. The DE fixture row (uid 4) proves findAllByIso2() FILTERS by
     * zn_country_iso_2 - it must NOT appear when parent="US".
     */
    #[Test]
    public function rendersOneOptionPerZoneOfParentIsoOrderedByNameAndFiltersOtherCountries(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="zone">'
            . '<option value="1">California</option>\n'
            . '<option value="2">New York</option>\n'
            . '<option value="3">Texas</option>\n'
            . '</select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" parent="US" />')
        );
    }

    /**
     * Positive path for a different parent iso, further proving the zn_country_iso_2 filter:
     * only the single DE zone (uid 4) is rendered, none of the US zones.
     */
    #[Test]
    public function rendersOnlyZonesMatchingTheGivenParentIso(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="zone">'
            . '<option value="4">Bayern</option>'
            . '\n</select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" parent="DE" />')
        );
    }

    /**
     * Positive path with a parent iso that has no zones: findAllByIso2() returns no rows,
     * so options stay empty and an optionless select is rendered.
     */
    #[Test]
    public function rendersEmptySelectWhenNoZoneMatchesTheGivenParentIso(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="zone"></select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" parent="FR" />')
        );
    }

    /**
     * $parent === null clause: with static_info_tables loaded (so isLoaded() is true and
     * cannot be the reason for the early return), omitting `parent` isolates the
     * `$this->arguments['parent'] === null` branch - initialize() returns before touching
     * the repository, leaving options unset, hence an optionless select. Because the US
     * fixture rows exist and the extension is loaded, a mutant dropping the null-check
     * would render zone options instead of this empty select.
     */
    #[Test]
    public function rendersEmptySelectWhenParentIsNotGivenEvenThoughExtensionIsLoaded(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="zone"></select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" />')
        );
    }

    /**
     * @param array<string, mixed> $variables
     */
    #[Test]
    #[DataProvider('selectedValueProvider')]
    public function marksTheBoundOptionAsSelected(string $template, string $expectedPattern, array $variables = []): void
    {
        self::assertMatchesRegularExpression($expectedPattern, $this->renderTemplate($template, $variables));
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2?: array<string, mixed>}>
     */
    public static function selectedValueProvider(): iterable
    {
        yield 'the option whose uid matches the bound value is marked selected' => [
            '<register:form.selectStaticCountryZones name="zone" parent="US" value="2" />',
            '#^<select name="zone">'
            . '<option value="1">California</option>\n'
            . '<option value="2" selected="selected">New York</option>\n'
            . '<option value="3">Texas</option>\n'
            . '</select>$#',
        ];
    }
}
