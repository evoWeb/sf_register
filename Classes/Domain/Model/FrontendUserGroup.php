<?php
namespace Evoweb\SfRegister\Domain\Model;

class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
{
    /**
     * @var int
     */
    protected $feloginRedirectPid;

    public function getFeloginRedirectPid(): int
    {
        return $this->feloginRedirectPid;
    }

    public function setFeloginRedirectPid(int $feloginRedirectPid)
    {
        $this->feloginRedirectPid = $feloginRedirectPid;
    }
}
