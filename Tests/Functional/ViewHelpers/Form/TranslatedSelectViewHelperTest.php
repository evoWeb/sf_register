<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * TranslatedSelectViewHelper overrides renderOptionTags() to translate the option
 * labels, which are label keys of the given extension. The labels used here are the
 * default (English) ones of EXT:sf_register/Resources/Private/Language/locallang.xlf.
 */
class TranslatedSelectViewHelperTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );

        // LocalizationUtility::translate() derives the locale from the site language of the global request
        $siteFinder = $this->get(SiteFinder::class);
        self::assertInstanceOf(SiteFinder::class, $siteFinder);
        $site = $siteFinder->getSiteByIdentifier('test');
        $this->request = $this->request
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    protected function renderTemplate(string $template): string
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
        $actual = (new TemplateView($context))->render();
        self::assertIsString($actual);
        return $actual;
    }

    #[Test]
    #[DataProvider('templateProvider')]
    public function rendersTranslatedOptionLabels(string $template, string $expectedPattern): void
    {
        self::assertMatchesRegularExpression($expectedPattern, $this->renderTemplate($template));
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function templateProvider(): iterable
    {
        yield 'option labels are translated' => [
            '<register:form.translatedSelect name="gender" extensionName="SfRegister" '
            . 'options="{1: \'gender_1\', 2: \'gender_2\'}" />',
            '#^<select name="gender">'
            . '<option value="1">Mr</option>\n'
            . '<option value="2">Mrs</option>\n'
            . '</select>$#',
        ];

        yield 'translated option keeps its selected state' => [
            '<register:form.translatedSelect name="gender" extensionName="SfRegister" '
            . 'options="{1: \'gender_1\', 2: \'gender_2\'}" value="2" />',
            '#^<select name="gender">'
            . '<option value="1">Mr</option>\n'
            . '<option value="2" selected="selected">Mrs</option>\n'
            . '</select>$#',
        ];
    }
}
