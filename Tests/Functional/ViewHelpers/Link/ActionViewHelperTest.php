<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Link;

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

        $this->createServerRequest();
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
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $request = $request->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $GLOBALS['TYPO3_REQUEST'] = $request;
        $extbaseRequest = new ExtbaseRequest($request);

        $contentObjectRenderer = $this->get(ContentObjectRenderer::class);
        self::assertInstanceOf(ContentObjectRenderer::class, $contentObjectRenderer);
        // Set the request explicitly so ContentObjectRenderer::getRequest() does not fall back to
        // $GLOBALS['TYPO3_REQUEST'] (deprecated since TYPO3 v14, removed in v15).
        $contentObjectRenderer->setRequest($request);
        $extbaseRequest = $extbaseRequest
            ->withAttribute('currentContentObject', $contentObjectRenderer);

        $renderingContextFactory = $this->get(RenderingContextFactory::class);
        self::assertInstanceOf(RenderingContextFactory::class, $renderingContextFactory);
        $context = $renderingContextFactory->create();
        $context->setAttribute(ServerRequestInterface::class, $extbaseRequest);
        $context->getTemplatePaths()->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        $result = (new TemplateView($context))->render();
        self::assertIsString($result);

        self::assertMatchesRegularExpression($expectedPattern, $result);
    }

    #[Test]
    #[DataProvider('templateProvider')]
    public function renderFrontendLinkWithCoreContext(string $template, string $expectedPattern): void
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $request = $request->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $renderingContextFactory = $this->get(RenderingContextFactory::class);
        self::assertInstanceOf(RenderingContextFactory::class, $renderingContextFactory);
        $context = $renderingContextFactory->create();
        $context->setAttribute(ServerRequestInterface::class, $request);
        $context->getTemplatePaths()->setTemplateSource('{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template);
        $result = (new TemplateView($context))->render();
        self::assertIsString($result);

        self::assertMatchesRegularExpression($expectedPattern, $result);
    }

    /**
     * @return iterable<array<int, string>>
     */
    public static function templateProvider(): iterable
    {
        yield [
            '<register:link.action pageUid="1" arguments="{user: 123}" extensionName="SfRegister" '
            . 'pluginName="Create" action="decline" controller="FeuserCreate" absolute="true">link '
            . 'text</register:link.action>',
            '#<a href="https://example.org/\?tx_sfregister_create%5Baction%5D=decline&amp;'
            . 'tx_sfregister_create%5Bcontroller%5D=FeuserCreate&amp;tx_sfregister_create%5Bhash%5D=[a-f0-9]+&amp;'
            . 'tx_sfregister_create%5Buser%5D=123&amp;cHash=[a-f0-9]+">link text</a>#s',
        ];

        // Regression guard: an array "user" argument (as used by
        // Resources/Private/Templates/Email/InviteToRegister.html for not-yet-persisted invitees)
        // must be reduced to its "email" key for the hash, not just string|int "user" values.
        yield [
            '<register:link.action pageUid="1" arguments="{user: {email: \'jane@example.org\'}}" '
            . 'extensionName="SfRegister" pluginName="Create" action="decline" controller="FeuserCreate" '
            . 'absolute="true">link text</register:link.action>',
            '#<a href="https://example.org/\?tx_sfregister_create%5Baction%5D=decline&amp;'
            . 'tx_sfregister_create%5Bcontroller%5D=FeuserCreate&amp;tx_sfregister_create%5Bhash%5D=[a-f0-9]+&amp;'
            . 'tx_sfregister_create%5Buser%5D%5Bemail%5D=jane%40example\.org&amp;cHash=[a-f0-9]+">'
            . 'link text</a>#s',
        ];
    }
}
