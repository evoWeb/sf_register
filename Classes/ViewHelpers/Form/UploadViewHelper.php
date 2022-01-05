<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\ViewHelpers\Form;

/*
 *  Copyright notice
 *
 *  (c) 2014 Helmut Hummel <helmut.hummel@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

class UploadViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    protected ?HashService $hashService = null;

    protected ?PropertyMapper $propertyMapper = null;

    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    public function injectPropertyMapper(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerTagAttribute(
            'disabled',
            'string',
            'Specifies that the input element should be disabled when the page loads'
        );
        $this->registerTagAttribute(
            'multiple',
            'string',
            'Specifies that the file input element should allow multiple selection of files'
        );
        $this->registerTagAttribute(
            'accept',
            'string',
            'Specifies the allowed file extensions to upload via comma-separated list, example ".png,.gif"'
        );
        $this->registerArgument(
            'errorClass',
            'string',
            'CSS class to set if there are errors for this ViewHelper',
            false,
            'f3-form-error'
        );
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('alwaysShowUpload', 'string', 'Whether the upload button should be always shown.');
        $this->registerTagAttribute('accept', 'string', 'Accepted file extensions.', false, '');
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

    /**
     * @param ObjectStorage|array $resources
     *
     * @return string
     */
    protected function renderPreview($resources): string
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

            $output .= '<input type="hidden" name="' . $this->getName()
                . '[submittedFile][resourcePointer]" value="'
                . htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue))
                . '"' . $resourcePointerIdAttribute . ' />';

            $this->templateVariableContainer->add('resource', $resource);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove('resource');
        }

        return $output;
    }

    /**
     * @param ObjectStorage|array $resources
     *
     * @return bool
     */
    protected function isRenderUpload($resources): bool
    {
        return is_null($resources)
            || ($resources instanceof ObjectStorage && count($resources) === 0)
            || (is_array($resources) && count($resources) === 0)
            || ($this->hasArgument('alwaysShowUpload') && $this->arguments['alwaysShowUpload']);
    }

    /**
     * Return a previously uploaded resource.
     * Return empty array if errors occurred during property mapping for this property.
     *
     * @return array
     */
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
                $result = [
                    $this->propertyMapper->convert($resource, FileReference::class),
                ];
            }
        }

        return $result;
    }
}
