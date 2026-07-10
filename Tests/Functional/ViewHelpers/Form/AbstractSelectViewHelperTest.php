<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\ViewHelpers\Form\AbstractSelectViewHelper;
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
 *
 * The `optionValueField`/`optionLabelField` arguments are registered only by the
 * concrete subclasses, therefore the getOptions() array-value branch is exercised
 * through the minimal SelectFixtureViewHelper defined at the end of this file, which
 * only adds those two argument registrations and otherwise keeps the abstract logic.
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
            '{namespace register=Evoweb\SfRegister\ViewHelpers}'
            . '{namespace testvh=Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form}'
            . $template
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
    public function rendersExpectedSelectMarkup(string $template, string $expectedPattern, array $variables = []): void
    {
        self::assertMatchesRegularExpression($expectedPattern, $this->renderTemplate($template, $variables));
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

        yield 'selectAllByDefault selects every option when no value is set' => [
            '<register:form.abstractSelect name="myField" multiple="1" selectAllByDefault="1" '
            . 'options="{0: \'Zero\', 1: \'One\', 2: \'Two\'}" />',
            '#^<input type="hidden" name="myField" value="" />'
            . '<select multiple="multiple" name="myField\[\]">'
            . '<option value="0" selected="selected">Zero</option>\n'
            . '<option value="1" selected="selected">One</option>\n'
            . '<option value="2" selected="selected">Two</option>\n'
            . '</select>$#',
        ];

        yield 'getOptions resolves key and label of array-value options via optionValueField/optionLabelField' => [
            '<testvh:selectFixture name="myField" optionValueField="id" optionLabelField="name" '
            . 'options="{r1: {id: \'10\', name: \'Ten\'}, r2: {id: \'20\', name: \'Twenty\'}}" />',
            '#^<select name="myField">'
            . '<option value="10">Ten</option>\n'
            . '<option value="20">Twenty</option>\n'
            . '</select>$#',
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

    /**
     * SOLL (post-30e771a): isSelected() force-selects ALL options when selectAllByDefault
     * is set, even if an explicit value is bound. The pre-fix code guards the force-select
     * with `empty($selectedValue)`, so with an explicit value only the matching option is
     * selected. Asserting the post-fix "all selected" behaviour therefore fails on df53334.
     */
    #[Test]
    public function selectAllByDefaultForceSelectsEveryOptionEvenWithExplicitValue(): void
    {
        self::markTestSkipped(
            'Pre-fix df53334: isSelected() only force-selects all when no value is set'
            . ' (empty($selectedValue) guard). 30e771a removes that guard so selectAllByDefault'
            . ' force-selects even with an explicit value. SOLL here asserts the 30e771a behavior'
            . ' (all selected). NOTE: the selectAllByDefault docblock still says "selected if none'
            . ' was set before" (= pre-fix), so this divergence needs human judgment in'
            . ' Roadmap-Schritt 2 - it may be an intended change or a regression. Behoben in'
            . ' 30e771a (Classes/ViewHelpers/Form/AbstractSelectViewHelper::isSelected).'
        );

        // $actual = $this->renderTemplate(
        //     '<register:form.abstractSelect name="myField" multiple="1" selectAllByDefault="1" '
        //     . 'options="{0: \'Zero\', 1: \'One\', 2: \'Two\'}" value="{selected}" />',
        //     ['selected' => [1]]
        // );
        // self::assertMatchesRegularExpression(
        //     '#^<input type="hidden" name="myField" value="" />'
        //     . '<select multiple="multiple" name="myField\[\]">'
        //     . '<option value="0" selected="selected">Zero</option>\n'
        //     . '<option value="1" selected="selected">One</option>\n'
        //     . '<option value="2" selected="selected">Two</option>\n'
        //     . '</select>$#',
        //     $actual
        // );
    }

    /**
     * SOLL (post-30e771a): array-value options require an optionValueField; if it is missing,
     * getOptions() throws a MissingArgumentException with code 1682693720 before any persistence
     * access. The pre-fix code has no such guard and falls through to
     * PersistenceManager::getIdentifierByObject() (AbstractSelectViewHelper.php:195), so the
     * intended MissingArgumentException is never raised.
     */
    #[Test]
    public function getOptionsThrowsMissingArgumentExceptionForArrayOptionsWithoutOptionValueField(): void
    {
        self::markTestSkipped(
            'Pre-fix df53334: array-value options without optionValueField do not throw the'
            . ' intended MissingArgumentException (code 1682693720). Missing the guard, the pre-fix'
            . ' getOptions() falls through to PersistenceManager::getIdentifierByObject()'
            . ' (AbstractSelectViewHelper.php:195), which raises an unrelated Error instead of the'
            . ' SOLL exception. SOLL asserts the 30e771a guard. Behoben in 30e771a'
            . ' (Classes/ViewHelpers/Form/AbstractSelectViewHelper::getOptions). Reaktivieren in'
            . ' Roadmap-Schritt 2.'
        );

        // $this->expectException(\TYPO3Fluid\Fluid\Core\ViewHelper\MissingArgumentException::class);
        // $this->expectExceptionCode(1682693720);
        // $this->renderTemplate(
        //     '<testvh:selectFixture name="myField" '
        //     . 'options="{r1: {id: \'10\', name: \'Ten\'}}" />'
        // );
    }
}

/**
 * Minimal concrete driver for the abstract getOptions() array-value branch.
 *
 * It only registers the optionValueField/optionLabelField arguments (as the shipped
 * subclasses do) and otherwise reuses the untouched AbstractSelectViewHelper logic, so
 * assertions target the abstract class rather than any subclass-specific rendering.
 */
class SelectFixtureViewHelper extends AbstractSelectViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.'
        );
    }
}
