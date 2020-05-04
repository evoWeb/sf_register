<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Services\Event;

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

use TYPO3\CMS\Core\Mail\MailMessage;

final class PreSubmitMailEvent
{
    /**
     * @var MailMessage
     */
    protected $mail;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * SendMailEvent constructor.
     *
     * @param MailMessage $mail
     * @param array $settings
     * @param array[FrontendUser] $arguments
     */
    public function __construct(MailMessage $mail, array $settings, array $arguments)
    {
        $this->mail = $mail;
        $this->settings = $settings;
        $this->arguments = $arguments;
    }

    /**
     * @return MailMessage
     */
    public function getMail(): MailMessage
    {
        return $this->mail;
    }

    /**
     * @return MailMessage
     */
    public function getResult(): MailMessage
    {
        return $this->mail;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
