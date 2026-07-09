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
 * AbstractSelectViewHelper is a concrete class (not declared abstract) that is
 * used directly by its RangeSelectViewHelper/SelectStatic*ViewHelper subclasses.
 * Since it registers all arguments needed to render a plain select box itself,
 * it can be driven directly through Fluid as `register:form.abstractSelect`
 * without needing a dedicated test subclass.
 */
class AbstractSelectViewHelperTest extends AbstractTestBase
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
    #[Test]
    #[DataProvider('templateProvider')]
    public function render(string $template, string $expectedPattern, array $variables = []): void
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
        $context->getTemplatePaths()
            ->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        foreach ($variables as $name => $value) {
            $context->getVariableProvider()->add($name, $value);
        }
        $actual = (new TemplateView($context))->render();
        self::assertIsString($actual);

        self::assertMatchesRegularExpression($expectedPattern, $actual);
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2?: array<string, mixed>}>
     */
    public static function templateProvider(): iterable
    {
        yield 'renderOptionTags creates one option tag per entry, unselected' => [
            '<register:form.abstractSelect name="myField" '
            . 'options="{alpha: \'Alpha\', beta: \'Beta\', gamma: \'Gamma\'}" />',
            '#^<select name="myField">'
            . '<option value="alpha">Alpha</option>\n'
            . '<option value="beta">Beta</option>\n'
            . '<option value="gamma">Gamma</option>\n'
            . '</select>$#',
        ];

        yield 'isSelected/getSelectedValue mark the matching option for a string value' => [
            '<register:form.abstractSelect name="myField" '
            . 'options="{alpha: \'Alpha\', beta: \'Beta\'}" value="beta" />',
            '#^<select name="myField">'
            . '<option value="alpha">Alpha</option>\n'
            . '<option value="beta" selected="selected">Beta</option>\n'
            . '</select>$#',
        ];

        yield 'isSelected/getSelectedValue mark the matching option for an int value' => [
            '<register:form.abstractSelect name="myField" '
            . 'options="{0: \'Zero\', 1: \'One\', 2: \'Two\'}" value="{selected}" />',
            '#^<select name="myField">'
            . '<option value="0">Zero</option>\n'
            . '<option value="1" selected="selected">One</option>\n'
            . '<option value="2">Two</option>\n'
            . '</select>$#',
            ['selected' => 1],
        ];

        yield 'isSelected/getSelectedValue mark all matching options for an array value (multiple select)' => [
            '<register:form.abstractSelect name="myField" multiple="1" '
            . 'options="{0: \'Zero\', 1: \'One\', 2: \'Two\'}" value="{selected}" />',
            '#^<input type="hidden" name="myField" value="" />'
            . '<select multiple="multiple" name="myField\[\]">'
            . '<option value="0">Zero</option>\n'
            . '<option value="1" selected="selected">One</option>\n'
            . '<option value="2" selected="selected">Two</option>\n'
            . '</select>$#',
            ['selected' => [1, 2]],
        ];

        yield 'renderPrependOptionTag adds a leading option with an empty value when only the label is set' => [
            '<register:form.abstractSelect name="myField" prependOptionLabel="Please choose" '
            . 'options="{alpha: \'Alpha\'}" />',
            '#^<select name="myField">'
            . '<option value="">Please choose</option>\n'
            . '<option value="alpha">Alpha</option>\n'
            . '</select>$#',
        ];

        yield 'renderPrependOptionTag uses prependOptionValue as value when set' => [
            '<register:form.abstractSelect name="myField" prependOptionLabel="Please choose" '
            . 'prependOptionValue="-1" options="{alpha: \'Alpha\'}" />',
            '#^<select name="myField">'
            . '<option value="-1">Please choose</option>\n'
            . '<option value="alpha">Alpha</option>\n'
            . '</select>$#',
        ];

        yield 'render produces the full select markup including the required attribute' => [
            '<register:form.abstractSelect name="myField" required="1" '
            . 'options="{alpha: \'Alpha\'}" />',
            '#^<select required="required" name="myField">'
            . '<option value="alpha">Alpha</option>\n'
            . '</select>$#',
        ];
    }
}
