<?php

namespace Evoweb\SfRegister\Domain\Model;

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

class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
{
    /**
     * @var int
     */
    protected $feloginRedirectPid = 0;

    public function getFeloginRedirectPid(): int
    {
        return (int)$this->feloginRedirectPid;
    }

    public function setFeloginRedirectPid(int $feloginRedirectPid)
    {
        $this->feloginRedirectPid = $feloginRedirectPid;
    }
}
