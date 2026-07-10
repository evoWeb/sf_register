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
 * RangeSelectViewHelper::initialize() builds the `options` array handed to the
 * inherited AbstractSelectViewHelper::render() from the `start`/`end`/`step`/`digits`
 * arguments via `array_map(..., range($start, $end, $step))`. Because array_map()
 * preserves the sequential integer keys produced by range(), the rendered
 * `<option value="...">` attributes are the 0-based position within the range, while
 * the option label is the (optionally zero-padded) formatted number itself.
 */
class RangeSelectViewHelperTest extends AbstractTestBase
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
     * @param array<string, mixed> $variables
     */
    #[Test]
    #[DataProvider('templateProvider')]
    public function rendersExpectedRangeSelectMarkup(string $template, string $expectedPattern, array $variables = []): void
    {
        self::assertMatchesRegularExpression($expectedPattern, $this->renderTemplate($template, $variables));
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2?: array<string, mixed>}>
     */
    public static function templateProvider(): iterable
    {
        yield 'initialize generates one option per number for the given start..end range (default step/digits)' => [
            '<register:form.rangeSelect name="myField" start="1" end="5" />',
            '#^<select name="myField">'
            . '<option value="0">01</option>\n'
            . '<option value="1">02</option>\n'
            . '<option value="2">03</option>\n'
            . '<option value="3">04</option>\n'
            . '<option value="4">05</option>\n'
            . '</select>$#',
        ];

        yield 'initialize honours a custom digits width, without truncating numbers longer than it' => [
            '<register:form.rangeSelect name="myField" start="8" end="10" digits="1" />',
            '#^<select name="myField">'
            . '<option value="0">8</option>\n'
            . '<option value="1">9</option>\n'
            . '<option value="2">10</option>\n'
            . '</select>$#',
        ];

        yield 'initialize honours a custom step across the range' => [
            '<register:form.rangeSelect name="myField" start="0" end="20" step="5" />',
            '#^<select name="myField">'
            . '<option value="0">00</option>\n'
            . '<option value="1">05</option>\n'
            . '<option value="2">10</option>\n'
            . '<option value="3">15</option>\n'
            . '<option value="4">20</option>\n'
            . '</select>$#',
        ];

        yield 'initialize generates a single option when start equals end' => [
            '<register:form.rangeSelect name="myField" start="5" end="5" />',
            '#^<select name="myField">'
            . '<option value="0">05</option>\n'
            . '</select>$#',
        ];

        $defaultRangeOptions = '';
        for ($number = 1; $number <= 20; $number++) {
            $defaultRangeOptions .= '<option value="' . ($number - 1) . '">' . sprintf('%02d', $number) . '</option>\n';
        }
        yield 'initialize falls back to the documented default range (start=1, end=20, step=1, digits=2)' => [
            '<register:form.rangeSelect name="myField" />',
            '#^<select name="myField">' . $defaultRangeOptions . '</select>$#',
        ];
    }
}
