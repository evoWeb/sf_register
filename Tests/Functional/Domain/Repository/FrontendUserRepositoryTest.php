<?php
namespace Evoweb\SfRegister\Tests\Functional\Domain\Repository;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUserRepositoryTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     */
    protected $fixture;

    /**
     * @var \Tx_Phpunit_Framework
     */
    private $testingFramework;

    public function setUp()
    {
        $this->testingFramework = new \Tx_Phpunit_Framework('fe_users');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            array('include_static_file' => 'EXT:sf_register/Configuration/TypoScript/')
        );
        $this->testingFramework->createFakeFrontEnd($pageUid);

        $extensionName = 'SfRegister';
        $pluginName = 'Form';
        $extensionSettings = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName];
        $extensionSettings['modules'][$pluginName]['controllers']['FeuserCreate'] = array(
            'actions' => array('form', 'preview', 'proxy', 'save', 'confirm', 'removeImage'),
            'nonCacheableActions' => array('form', 'preview', 'proxy', 'save', 'confirm', 'removeImage'),
        );

        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->run(
            '',
            array(
                'userFunc' => '\TYPO3\CMS\Extbase\Core\Bootstrap->run',
                'extensionName' => $extensionName,
                'pluginName' => $pluginName,
            )
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        $this->fixture = new \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository($objectManager);
    }

    public function tearDown()
    {
        $this->testingFramework->cleanUp();

        unset($this->fixture, $this->testingFramework);
    }
}
