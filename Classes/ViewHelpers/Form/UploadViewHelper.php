<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\ViewHelpers\Form;

use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Form\Security\HashScope;

class UploadViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    public function __construct(
        protected HashService $hashService,
        protected PropertyMapper $propertyMapper
    ) {
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'multiple',
            'string',
            'Specifies that the file input element should allow multiple selection of files'
        );
        $this->registerArgument(
            'errorClass',
            'string',
            'CSS class to set if there are errors for this ViewHelper',
            false,
            'f3-form-error'
        );
        $this->registerArgument(
            'alwaysShowUpload',
            'string',
            'Whether the upload button should be always shown.'
        );
    }

    public function render(): string
    {
        $output = '';

        $resources = $this->getUploadedResource();

        if (count($resources)) {
            $output .= $this->renderPreview($resources);
        }

        if ($this->isRenderUpload($resources)) {
            $name = $this->getName();
            $allowedFields = ['name', 'type', 'tmp_name', 'error', 'size'];
            foreach ($allowedFields as $fieldName) {
                $this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']');
            }
            $this->tag->addAttribute('type', 'file');

            if (isset($this->arguments['multiple'])) {
                $this->tag->addAttribute('name', $name . '[]');
            } else {
                $this->tag->addAttribute('name', $name);
            }

            $this->setErrorClassAttribute();
            $output .= $this->tag->render();
        }

        return $output;
    }

    protected function renderPreview(array $resources): string
    {
        $output = '';

        /** @var FileReference $resource */
        foreach ($resources as $resource) {
            $resourcePointerIdAttribute = '';
            if ($this->hasArgument('id')) {
                $resourcePointerIdAttribute = ' id="' . htmlspecialchars($this->arguments['id']) . '-file-reference"';
            }
            $resourcePointerValue = $resource->getUid();
            if ($resourcePointerValue === null) {
                // Newly created file reference which is not persisted yet.
                // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
            }

            $this->registerFieldNameForFormTokenGeneration($this->getName() . '[submittedFile][resourcePointer]');

            $output .= '<input type="hidden" name="' . htmlspecialchars($this->getName()) . '[submittedFile][resourcePointer]" value="'
                . htmlspecialchars($this->hashService->appendHmac(
                    (string)$resourcePointerValue,
                    UploadedFileReferenceConverter::RESOURCE_POINTER_PREFIX
                ))
                . '"' . $resourcePointerIdAttribute . ' />';

            $this->templateVariableContainer->add('resource', $resource);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove('resource');
        }

        return $output;
    }

    protected function isRenderUpload(array $resources): bool
    {
        return count($resources) === 0
            || (
                $this->hasArgument('alwaysShowUpload')
                && $this->arguments['alwaysShowUpload']
            );
    }

    protected function getUploadedResource(): array
    {
        $result = [];

        if (!$this->getMappingResultsForProperty()->hasErrors()) {
            $resource = $this->getValueAttribute();

            if ($resource instanceof FileReference) {
                $result = [$resource];
            } elseif ($resource instanceof ObjectStorage) {
                $result = $resource->toArray();
            } elseif ($resource !== null) {
                try {
                    $result = [$this->propertyMapper->convert($resource, FileReference::class)];
                } catch (\Exception) {
                }
            }
        }

        return $result;
    }
}
