<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers\Form;

use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * UploadViewHelper::getUploadedResource() reads the bound value (here passed directly via
 * the `value` argument, since no `<f:form object="...">` is used) and returns it as an
 * array of FileReference resources: as-is for a single FileReference, unwrapped for an
 * ObjectStorage, converted via PropertyMapper otherwise, or an empty array when nothing
 * is bound or the property mapping for it has errors.
 *
 * UploadViewHelper::renderPreview() is only invoked by render() when that array is
 * non-empty. It emits, per resource, a hidden `[submittedFile][resourcePointer]` input
 * (HMAC-signed via HashService, using the resource's uid, or - for a not-yet-persisted
 * FileReference - "file:" + the underlying sys_file uid) followed by the rendered tag
 * content with `resource` bound as a template variable. Without any bound resource,
 * render() falls back to `isRenderUpload()` and renders the plain `<input type="file">`
 * element instead, with no preview markup at all.
 *
 * 30e771a only touches this file with phpstan-only changes that have no observable
 * runtime effect:
 * - the `id` attribute guard in renderPreview() additionally checks
 *   `is_string($this->arguments['id']) && $this->arguments['id'] !== ''`. This is
 *   unreachable in both versions: `id` is never a registered argument of
 *   UploadViewHelper (only registered arguments end up in $this->arguments; `id` is
 *   collected as an additionalArgument instead - see
 *   TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker::invoke()), so
 *   `hasArgument('id')` is always false and $resourcePointerIdAttribute always stays ''.
 * - `$this->templateVariableContainer?->add(...)`/`?->remove(...)` add a nullsafe
 *   operator; templateVariableContainer is always set via setRenderingContext() when
 *   rendering through a TemplateView, so this never changes behaviour here.
 * - `is_string($content) ? $content : ''` guards renderChildren()'s return value; with
 *   the plain-text/element children used here it always returns a string, so the
 *   ternary is a no-op.
 * - the `/** @var FileReference[] $result *\/` annotations in getUploadedResource() are
 *   pure phpstan hints with zero runtime effect.
 *
 * Since none of these changes affect observable behaviour, no Bug-/Deprecation-Protokoll
 * is needed - the tests below assert identical behaviour on both sides of 30e771a.
 */
class UploadViewHelperTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/sys_file_storage.csv');

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
     * Builds a not-yet-persisted Extbase FileReference wrapping a real sys_file, the same
     * way FileTest builds FAL fixtures (storage->addFile() + ResourceFactory::createFileReferenceObject()
     * with uid=0). Since the returned object's own uid is null, this exercises the
     * "newly created file reference which is not persisted yet" branch of renderPreview(),
     * which builds the resource pointer from the underlying file's uid instead.
     */
    protected function createUnpersistedFileReference(string $filename): ExtbaseFileReference
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);

        $localFile = $this->createJpegFile($filename);
        $file = $storage->addFile($localFile, $storage->getRootLevelFolder(), $filename);

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = $this->get(ResourceFactory::class);
        $coreFileReference = $resourceFactory->createFileReferenceObject([
            'uid' => 0,
            'uid_local' => $file->getUid(),
        ]);

        $fileReference = new ExtbaseFileReference();
        $fileReference->setOriginalResource($coreFileReference);

        return $fileReference;
    }

    protected function createJpegFile(string $filename): string
    {
        // Minimal valid 1x1 JPEG so the storage mime-type consistency check passes.
        $bytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'
            . 'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QA'
            . 'FAABAAAAAAAAAAAAAAAAAAAAAv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8A'
            . 'fwD/2Q=='
        );
        $path = $this->instancePath . '/typo3temp/var/transient/';
        GeneralUtility::mkdir_deep($path);
        $testFilename = $path . $filename;
        file_put_contents($testFilename, (string)$bytes);
        return $testFilename;
    }

    #[Test]
    public function renderPreviewRendersMarkupAndGetUploadedResourceReturnsBoundResourceWhenFileReferenceIsPresent(): void
    {
        $fileReference = $this->createUnpersistedFileReference('preview.jpg');
        $originalFileUid = $fileReference->getOriginalResource()->getOriginalFile()->getUid();

        /** @var HashService $hashService */
        $hashService = $this->get(HashService::class);
        $expectedPointerValue = $hashService->appendHmac(
            'file:' . $originalFileUid,
            UploadedFileReferenceConverter::RESOURCE_POINTER_PREFIX
        );

        $actual = $this->renderTemplate(
            '<register:form.upload name="myField" value="{resource}">'
            . '<span>{resource.originalResource.originalFile.name}</span>'
            . '</register:form.upload>',
            ['resource' => $fileReference]
        );

        self::assertMatchesRegularExpression(
            '#^<input type="hidden" name="myField\[submittedFile\]\[resourcePointer\]" value="'
            . preg_quote($expectedPointerValue, '#')
            . '" /><span>preview\.jpg</span>$#',
            $actual
        );
    }

    #[Test]
    public function renderPreviewRendersNoMarkupAndGetUploadedResourceReturnsEmptyArrayWithoutFileReference(): void
    {
        $actual = $this->renderTemplate(
            '<register:form.upload name="myField">'
            . '<span>should-not-be-rendered</span>'
            . '</register:form.upload>'
        );

        self::assertSame('<input type="file" name="myField" />', $actual);
    }
}
