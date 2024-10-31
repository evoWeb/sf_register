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

namespace Evoweb\SfRegister\ViewHelpers;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class ApplicationContextViewHelper extends AbstractConditionViewHelper
{
    protected Resolver $resolver;

    public function __construct()
    {
        $contextVariables = [
            'applicationContext' => Environment::getContext()->__toString(),
        ];
        $this->resolver = new Resolver('typoscript', $contextVariables);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('environment', 'string', 'Application context to check against', false, 'Production');
    }

    public function render()
    {
        if ($this->resolver->evaluate('applicationContext matches \'/^' . $this->arguments['environment'] . '/\'')) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }
}
