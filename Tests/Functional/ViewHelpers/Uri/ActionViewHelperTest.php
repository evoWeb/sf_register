<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Uri;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

class ActionViewHelperTest extends AbstractTestBase
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
    public function renderWithExtbaseContext(string $template, string $expectedPattern): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $extbaseRequest = new ExtbaseRequest($GLOBALS['TYPO3_REQUEST']);
        $extbaseRequest = $extbaseRequest
            ->withAttribute('currentContentObject', $this->get(ContentObjectRenderer::class));

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->setAttribute(ServerRequestInterface::class, $extbaseRequest);
        $context->getTemplatePaths()->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        $result = (new TemplateView($context))->render();

        self::assertMatchesRegularExpression($expectedPattern, $result);
    }

    #[Test]
    #[DataProvider('templateProvider')]
    public function renderFrontendLinkWithCoreContext(string $template, string $expectedPattern): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->setAttribute(ServerRequestInterface::class, $GLOBALS['TYPO3_REQUEST']);
        $context->getTemplatePaths()->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        $result = (new TemplateView($context))->render();

        self::assertMatchesRegularExpression($expectedPattern, $result);
    }

    public static function templateProvider(): array
    {
        return [
            [
                '<register:uri.action pageUid="1" arguments="{user: 123}" extensionName="SfRegister" pluginName="Create" action="decline" controller="FeuserCreate" absolute="true" />',
                '#^https://example.org/\?tx_sfregister_create%5Baction%5D=decline&tx_sfregister_create%5Bcontroller%5D=FeuserCreate&tx_sfregister_create%5Bhash%5D=[a-f0-9]+&tx_sfregister_create%5Buser%5D=123&cHash=[a-f0-9]+$#',
            ],
        ];
    }
}
