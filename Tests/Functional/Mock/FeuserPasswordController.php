<?php

namespace Evoweb\SfRegister\Tests\Functional\Mock;

use Evoweb\SfRegister\Controller\FeuserPasswordController as BaseFeuserPasswordController;
use Evoweb\SfRegister\Tests\Functional\Traits\SettableCallable;

class FeuserPasswordController extends BaseFeuserPasswordController
{
    use SettableCallable;
}
