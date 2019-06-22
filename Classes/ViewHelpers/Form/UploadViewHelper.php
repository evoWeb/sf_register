<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/***************************************************************
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
 ***************************************************************/

/**
 * Class UploadViewHelper
 */
class UploadViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\UploadViewHelper
{
    /**
     * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param \TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService
     */
    public function injectHashService(\TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper
     */
    public function injectPropertyMapper(\TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * Initialize the arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('alwaysShowUpload', 'string', 'Whether the upload button should be always shown.');
    }

    /**
     * Render the upload field including possible resource pointer
     *
     * @return string
     * @api
     */
    public function render()
    {
        $output = '';

        $resources = $this->getUploadedResource();

        if (!is_null($resources)) {
            $output .= $this->renderPreview($resources);
        }

        if ($this->isRenderUpload($resources)) {
            $output .= parent::render();
        }

        return $output;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $resources
     *
     * @return string
     */
    protected function renderPreview($resources)
    {
        $output = '';

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
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array $resources
     *
     * @return bool
     */
    protected function isRenderUpload($resources)
    {
        $null = is_null($resources);
        $objectStorage = $resources instanceof ObjectStorage && empty($resources->toArray());
        $fileReference = $resources instanceof FileReference && empty($resources->getOriginalResource()->toArray());
        $array = is_array($resources) && empty($resources);
        $argument = $this->hasArgument('alwaysShowUpload') && $this->arguments['alwaysShowUpload'];
        return $null || $objectStorage || $fileReference || $array || $argument;
    }

    /**
     * Return a previously uploaded resource.
     * Return NULL if errors occurred during property mapping for this property.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|array
     */
    protected function getUploadedResource()
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }

        $resource = $this->getValueAttribute();
        if ($resource instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
            return [$resource];
        } elseif ($resource instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
            return $resource;
        }

        return [$this->propertyMapper->convert($resource, \TYPO3\CMS\Extbase\Domain\Model\FileReference::class)];
    }
}
