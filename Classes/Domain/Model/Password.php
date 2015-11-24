<?php
namespace Evoweb\SfRegister\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A password object for validation
 */
class Password extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * Password
     *
     * @var string
     */
    protected $password;

    /**
     * Repetition of Password
     *
     * @var string
     */
    protected $passwordRepeat;

    /**
     * Old password
     *
     * @var string
     */
    protected $oldPassword;

    /**
     * Getter for password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Setter for password
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Getter for passwordAgain
     *
     * @return string
     */
    public function getPasswordRepeat()
    {
        return $this->passwordRepeat;
    }

    /**
     * Setter for passwordRepeat
     *
     * @param string $passwordRepeat
     * @return void
     */
    public function setPasswordRepeat($passwordRepeat)
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    /**
     * Getter for oldPassword
     *
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * Setter for oldPassword
     *
     * @param string $oldPassword
     * @return void
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;
    }
}
