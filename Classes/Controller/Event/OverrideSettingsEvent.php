<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Controller\Event;

class OverrideSettingsEvent
{
    public function __construct(
        protected array $settings,
        protected readonly string $controllerName
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
}
