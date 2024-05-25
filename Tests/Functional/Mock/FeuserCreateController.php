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

namespace Evoweb\SfRegister\Tests\Functional\Mock;

use Evoweb\SfRegister\Controller\FeuserCreateController as BaseFeuserCreateController;
use Evoweb\SfRegister\Tests\Functional\Traits\SettableCallable;

class FeuserCreateController extends BaseFeuserCreateController
{
    use SettableCallable;
}
