<?php

namespace Evoweb\SfRegister\Tests\Functional\Mock;

use Evoweb\SfRegister\Controller\FeuserCreateController as BaseFeuserCreateController;
use Evoweb\SfRegister\Tests\Functional\SettableCallable;

class FeuserCreateController extends BaseFeuserCreateController
{
    use SettableCallable;
}
