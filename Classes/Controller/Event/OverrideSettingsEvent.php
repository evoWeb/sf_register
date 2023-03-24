<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Controller\Event;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class OverrideSettingsEvent
{
    public function __construct(
        protected array $settings,
        protected readonly string $controllerName,
        protected ContentObjectRenderer $contentObject
    ) {
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    public function getContentObject(): ContentObjectRenderer
    {
        return $this->contentObject;
    }
}
