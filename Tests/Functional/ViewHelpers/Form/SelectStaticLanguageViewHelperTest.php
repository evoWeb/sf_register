<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * SelectStaticLanguageViewHelper::initialize() builds the `options` array handed to
 * the inherited AbstractSelectViewHelper::render() from the `static_languages` rows,
 * queried via the Extbase ORM repository StaticLanguageRepository:
 *
 *   if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
 *       return;
 *   }
 *   if (count($this->arguments['allowedLanguages'])) {
 *       $options = $this->languageRepository->findByLgCollateLocale($this->arguments['allowedLanguages']);
 *   } else {
 *       $options = $this->languageRepository->findAll();
 *   }
 *
 * Each row keeps its default optionValueField ("lgIso2" -> column lg_iso_2) and
 * optionLabelField ("lgNameEn" -> column lg_name_en).
 *
 * `ExtensionManagementUtility::isLoaded('static_info_tables')` checks a declared TYPO3
 * extension-KEY, not the sjbr/static-info-tables composer package (which is only a
 * "suggest" of sf_register, not installed in this extension's own test container).
 * This test therefore loads a minimal stub extension declaring exactly that
 * extension-key plus the `static_languages` table schema AND a matching minimal TCA
 * definition (Tests/Fixtures/Extensions/static_info_tables/), which makes isLoaded()
 * true and lets Extbase build a DataMap for the Evoweb\SfRegister\Domain\Model\
 * StaticLanguage model (mapped onto table "static_languages" via Configuration/
 * Extbase/Persistence/Classes.php) so the real repository query runs against the
 * fixture rows. That covers both branches of the positive path: findAll() (no
 * allowedLanguages) and findByLgCollateLocale() (allowedLanguages given), including
 * that the latter FILTERS out non-matching rows.
 */
class SelectStaticLanguageViewHelperTest extends AbstractTestBase
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
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/static_languages.csv');

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
     * Positive path, findAll() branch: with no `allowedLanguages` given, initialize()
     * builds one option per fixture row (value = lg_iso_2, label = lg_name_en),
     * covering all three languages of the fixture.
     */
    #[Test]
    public function rendersOneOptionPerLanguageWhenNoAllowedLanguagesAreGiven(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="language">'
            . '<option value="de">German</option>\n'
            . '<option value="en">English</option>\n'
            . '<option value="fr">French</option>\n'
            . '</select>$#',
            $this->renderTemplate('<register:form.selectStaticLanguage name="language" />')
        );
    }

    /**
     * Positive path, findByLgCollateLocale() branch: with `allowedLanguages` given,
     * initialize() only renders the matching rows (German + French), proving the
     * lg_collate_locale filter - the English fixture row (lg_collate_locale "en_US")
     * must NOT appear even though it exists in the fixture.
     */
    #[Test]
    public function rendersOnlyLanguagesMatchingTheGivenAllowedLanguagesAndFiltersOthers(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="language">'
            . '<option value="de">German</option>\n'
            . '<option value="fr">French</option>\n'
            . '</select>$#',
            $this->renderTemplate(
                '<register:form.selectStaticLanguage name="language" allowedLanguages="{0: \'de_DE\', 1: \'fr_FR\'}" />'
            )
        );
    }

    /**
     * Further proof of the lg_collate_locale filter with a single allowed language:
     * only that one language is rendered, none of the other fixture rows.
     */
    #[Test]
    public function rendersOnlyTheSingleLanguageMatchingOneAllowedLanguage(): void
    {
        self::assertMatchesRegularExpression(
            '#^<select name="language">'
            . '<option value="en">English</option>'
            . '\n</select>$#',
            $this->renderTemplate(
                '<register:form.selectStaticLanguage name="language" allowedLanguages="{0: \'en_US\'}" />'
            )
        );
    }
}
