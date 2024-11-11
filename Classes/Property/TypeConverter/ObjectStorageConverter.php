<?php

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

namespace Evoweb\SfRegister\Property\TypeConverter;

use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectStorageConverter as ExtbaseObjectStorageConverter;

class ObjectStorageConverter extends ExtbaseObjectStorageConverter
{
    /**
     * Return the source, if it is an array, otherwise an empty array.
     * Filter out empty uploads
     *
     * @return array<string, mixed>
     */
    public function getSourceChildPropertiesToBeConverted(mixed $source): array
    {
        $propertiesToConvert = [];

        // @todo: Find a nicer way to throw away empty uploads
        foreach ($source as $propertyName => $propertyValue) {
            if ($this->isUploadType($propertyValue)) {
                if (
                    $propertyValue['error'] !== \UPLOAD_ERR_NO_FILE
                    || isset($propertyValue['submittedFile']['resourcePointer'])
                ) {
                    $propertiesToConvert[$propertyName] = $propertyValue;
                }
            } else {
                $propertiesToConvert[$propertyName] = $propertyValue;
            }
        }

        return $propertiesToConvert;
    }

    protected function isUploadType(mixed $propertyValue): bool
    {
        return is_array($propertyValue) && isset($propertyValue['tmp_name']) && isset($propertyValue['error']);
    }
}
