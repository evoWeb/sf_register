<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * SelectStaticCountryZonesViewHelper::initialize() builds the `options` array handed to
 * the inherited AbstractSelectViewHelper::render() from the `static_country_zones` rows
 * matching the given `parent` (a country ISO-2 code) via
 * StaticCountryZoneRepository::findAllByIso2(). Each row keeps its default
 * optionValueField ("uid") and optionLabelField ("zn_name_local"), and the repository
 * orders rows by "zn_name_local".
 *
 *   if ($this->arguments['parent'] === null || !ExtensionManagementUtility::isLoaded('static_info_tables')) {
 *       return;
 *   }
 *
 * initialize() only fills `options` when BOTH a non-null `parent` argument is given AND
 * the `static_info_tables` extension (which owns the `static_country_zones` table) is
 * loaded; otherwise it returns early and `options` stays unset, so render() falls back to
 * an empty select. Both halves of that OR-guard are independently reachable and are
 * covered below without needing the static_country_zones table.
 *
 * sf-register only "suggest"s sjbr/static-info-tables in composer.json (not
 * require/require-dev), so this isolated functional test composer root does not install
 * it: ExtensionManagementUtility::isLoaded('static_info_tables') is false here, which is
 * used directly to cover the "extension not loaded" branch. The remaining branch (options
 * actually built from static_country_zones rows) needs the extension loaded and its table
 * populated, which is not available in this environment and is skipped per test with a
 * dedicated reason.
 */
class SelectStaticCountryZonesViewHelperTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/static_country_zones.csv');
        }

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
     * Covers the first half of the OR-guard (`$this->arguments['parent'] === null`),
     * which is reachable regardless of whether static_info_tables is loaded.
     */
    #[Test]
    public function rendersEmptySelectWhenParentIsNotGiven(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="zone"></select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" />')
        );
    }

    /**
     * Covers the second half of the OR-guard
     * (`!ExtensionManagementUtility::isLoaded('static_info_tables')`) with a `parent` given,
     * which is exactly the state of this functional test environment (sf-register only
     * "suggest"s sjbr/static-info-tables, so it is never installed/loaded here).
     */
    #[Test]
    public function rendersEmptySelectWhenStaticInfoTablesExtensionIsNotLoaded(): void
    {
        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped(
                'static_info_tables is loaded in this environment, so the'
                . ' "!ExtensionManagementUtility::isLoaded(\'static_info_tables\')" branch of'
                . ' initialize() is not reachable here.'
            );
        }

        self::assertMatchesRegularExpression(
            '#^<select name="zone"></select>$#',
            $this->renderTemplate('<register:form.selectStaticCountryZones name="zone" parent="US" />')
        );
    }

    /**
     * @param array<string, mixed> $variables
     */
    #[Test]
    #[DataProvider('templateProvider')]
    public function rendersExpectedCountryZonesSelectMarkup(string $template, string $expectedPattern, array $variables = []): void
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped(
                'static_info_tables extension (providing the static_country_zones table read by'
                . ' StaticCountryZoneRepository::findAllByIso2()) is not available in this functional'
                . ' test environment: sf-register only "suggest"s sjbr/static-info-tables in'
                . ' composer.json (not require/require-dev), so the isolated test composer root does'
                . ' not install it. Confirmed empirically: adding'
                . ' "sjbr/static-info-tables" to $testExtensionsToLoad fails bootstrap with'
                . ' "Test extension path .../public/sjbr/static-info-tables not found". Without the'
                . ' extension loaded, SelectStaticCountryZonesViewHelper::initialize() always returns'
                . ' early (see rendersEmptySelectWhenStaticInfoTablesExtensionIsNotLoaded), so the'
                . ' options-building branch cannot be exercised without changing production'
                . ' composer.json, which is out of scope for a Tests/-only change.'
            );
        }

        self::assertMatchesRegularExpression($expectedPattern, $this->renderTemplate($template, $variables));
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2?: array<string, mixed>}>
     */
    public static function templateProvider(): iterable
    {
        yield 'initialize builds one option per zone matching parent, ordered by zn_name_local, value=uid label=zn_name_local' => [
            '<register:form.selectStaticCountryZones name="zone" parent="US" />',
            '#^<select name="zone">'
            . '<option value="1">California</option>\n'
            . '<option value="2">New York</option>\n'
            . '<option value="3">Texas</option>\n'
            . '</select>$#',
        ];

        yield 'initialize filters zones to only those matching the given parent iso2' => [
            '<register:form.selectStaticCountryZones name="zone" parent="DE" />',
            '#^<select name="zone">'
            . '<option value="4">Bayern</option>\n'
            . '</select>$#',
        ];

        yield 'initialize leaves options unset (empty select) when no matching zone exists for parent' => [
            '<register:form.selectStaticCountryZones name="zone" parent="FR" />',
            '#^<select name="zone"></select>$#',
        ];
    }
}
