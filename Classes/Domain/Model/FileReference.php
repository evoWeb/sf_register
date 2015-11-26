<?php
namespace Evoweb\SfRegister\Domain\Model;

/**
 * Class FileReference
 *
 * @package Evoweb\SfRegister\Domain\Model
 */
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
    /**
     * Uid of the referenced sys_file. Needed for extbase to serialize the
     * reference correctly.
     *
     * @var int
     */
    protected $uidLocal;

    /**
     * Getter
     *
     * @return int
     */
    public function getUidLocal()
    {
        return $this->uidLocal;
    }

    /**
     * Setter
     *
     * @param int $uidLocal
     */
    public function setUidLocal($uidLocal)
    {
        $this->uidLocal = $uidLocal;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileReference $originalResource
     * @return void
     */
    public function setOriginalResource(\TYPO3\CMS\Core\Resource\FileReference $originalResource)
    {
        $this->originalResource = $originalResource;
        $this->uidLocal = (int)$originalResource->getOriginalFile()->getUid();
    }
}
