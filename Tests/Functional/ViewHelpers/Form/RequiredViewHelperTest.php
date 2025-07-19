<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

class RequiredViewHelperTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');

        $this->initializeRequest();
        $this->initializeFrontendTypoScript();

        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );
    }

    #[Test]
    #[DataProvider('templateProvider')]
    public function renderRequiredCharacter(string $template, ?string $expected, array $validation): void
    {
        $this->request = $this->request
            ->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $extbaseRequest = new ExtbaseRequest($this->request);
        $extbaseRequest = $extbaseRequest
            ->withAttribute('currentContentObject', $this->get(ContentObjectRenderer::class));

        /** @var FrontendTypoScript $frontendTypoScript */
        $frontendTypoScript = $this->request->getAttribute('frontend.typoscript');
        $setup = $frontendTypoScript->getSetupArray();
        $setup['plugin.']['tx_sfregister.']['settings.']['validation.'] = $validation;
        $frontendTypoScript->setSetupArray($setup);

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->setAttribute(ServerRequestInterface::class, $extbaseRequest);
        $context->getTemplatePaths()->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        $context->setControllerName(
            ExtensionUtility::resolveControllerAliasFromControllerClassName(FeuserCreateController::class)
        );
        $actual = (new TemplateView($context))->render();

        self::assertEquals($expected, $actual);
    }

    public static function templateProvider(): iterable
    {
        yield [
            'template' => '<register:form.required fieldName="username"><f:then>*</f:then></register:form.required>',
            'expected' => '*',
            'validation' => [
                'create.' => [
                    'username' => RequiredValidator::class
                ]
            ]
        ];

        yield [
            'template' => '<register:form.required fieldName="username">*</register:form.required>',
            'expected' => '*',
            'validation' => [
                'create.' => [
                    'username' => RequiredValidator::class
                ]
            ]
        ];

        yield [
            'template' => '<register:form.required fieldName="username" then="*"/>',
            'expected' => '*',
            'validation' => [
                'create.' => [
                    'username' => RequiredValidator::class
                ]
            ]
        ];

        yield [
            'template' => '<register:form.required fieldName="username"><f:then>*</f:then></register:form.required>',
            'expected' => null,
            'validation' => [
                'create.' => [
                    'firstName' => RequiredValidator::class
                ]
            ]
        ];

        yield [
            'template' => '<register:form.required fieldName="username"><f:else>*</f:else></register:form.required>',
            'expected' => '*',
            'validation' => [
                'create.' => [
                    'firstName' => RequiredValidator::class
                ]
            ]
        ];

        yield [
            'template' => '<register:form.required fieldName="username" else="*"/>',
            'expected' => '*',
            'validation' => [
                'create.' => [
                    'firstName' => RequiredValidator::class
                ]
            ]
        ];
    }
}
