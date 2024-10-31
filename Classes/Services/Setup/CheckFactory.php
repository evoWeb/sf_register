<?php

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

namespace Evoweb\SfRegister\Services\Setup;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CheckFactory
{
    public function __construct(protected array $configuration) {}

    public function getCheckInstances(): array
    {
        $checks = [];

        foreach ($this->configuration['checks'] as $checkClassname) {
            $checks[] = GeneralUtility::makeInstance($checkClassname);
        }

        return $checks;
    }
}
