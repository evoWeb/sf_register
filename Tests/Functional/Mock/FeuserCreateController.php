<?php

namespace Evoweb\SfRegister\Tests\Functional\Mock;

use Evoweb\SfRegister\Controller\FeuserCreateController as BaseFeuserCreateController;
use Evoweb\SfRegister\Tests\Functional\Traits\SettableCallable;

class FeuserCreateController extends BaseFeuserCreateController
{
    use SettableCallable;
}
