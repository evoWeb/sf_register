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

use Evoweb\SfRegister\Controller\FeuserController;
use Psr\Http\Message\ResponseInterface;

final class InitializeActionEvent
{
    public function __construct(
        protected readonly FeuserController $controller,
        protected readonly array $settings,
        protected ?ResponseInterface $response
    ) {}

    public function getController(): FeuserController
    {
        return $this->controller;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
