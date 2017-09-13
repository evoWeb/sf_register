<?php
namespace Evoweb\SfRegister\Domain\Model;

class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
{
    /**
     * @var int
     */
    protected $feloginRedirectPid;

    /**
     * Getter for feloginRedirectPid
     *
     * @return int
     */
    public function getFeloginRedirectPid()
    {
        return $this->feloginRedirectPid;
    }

    /**
     * Setter for feloginRedirectPid
     *
     * @param int $feloginRedirectPid
     *
     * @return void
     */
    public function setFeloginRedirectPid($feloginRedirectPid)
    {
        $this->feloginRedirectPid = $feloginRedirectPid;
    }
}
