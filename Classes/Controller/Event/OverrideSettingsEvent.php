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

namespace Evoweb\SfRegister\Controller\Event;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class OverrideSettingsEvent
{
    public function __construct(
        protected array $settings,
        protected readonly string $controllerName,
        protected readonly ContentObjectRenderer $contentObject
    ) {
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getContentObject(): ContentObjectRenderer
    {
        return $this->contentObject;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
