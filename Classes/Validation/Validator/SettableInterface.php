<?php

namespace Evoweb\SfRegister\Validation\Validator;

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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;

interface SettableInterface
{
    /**
     * Setter for model
     *
     * @param FrontendUser|Password $model
     */
    public function setModel(FrontendUser|Password $model);

    public function setPropertyName(string $propertyName);
}
