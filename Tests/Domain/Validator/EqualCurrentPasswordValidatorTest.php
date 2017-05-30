<?php
namespace Evoweb\SfRegister\Tests\Domain\Validator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This class is a backport of the corresponding class of FLOW3.
 *  All credits go to the v5 team.
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class EqualCurrentPasswordValidatorTest
 */
class EqualCurrentPasswordValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $fixture;

    /**
     * @var \Tx_Phpunit_Framework
     */
    private $testingFramework;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->testingFramework = new \Tx_Phpunit_Framework('fe_users');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            array('include_static_file' => 'EXT:sf_register/Configuration/TypoScript/maximal/')
        );
        $this->testingFramework->createFakeFrontEnd($pageUid);

        $this->fixture = $this->getAccessibleMock(
            'Evoweb\\SfRegister\\Domain\\Validator\\EqualCurrentPasswordValidator',
            array('dummy')
        );
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->testingFramework->cleanUp();

        unset($this->fixture, $this->testingFramework);
    }

    /**
     * @test
     * @return void
     */
    public function settingsContainsValidTyposcriptSettings()
    {
        $this->assertArrayHasKey(
            'badWordList',
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    /**
     * @test
     * @return void
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        $this->assertFalse(
            $this->fixture->_call('isUserLoggedIn')
        );
    }

    /**
     * @test
     * @return void
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', array('password' => 'testOld'));

        $this->assertTrue(
            $this->fixture->_call('isUserLoggedIn')
        );
    }

    /**
     * @test
     * @return void
     */
    public function loggedinUserFoundInDbHasEqualUnencryptedPassword()
    {
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }
        $this->fixture->_set('settings', $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);

        $expected = 'myFancyPassword';

        $userId = $this->testingFramework->createAndLoginFrontEndUser('', array('password' => $expected));

        $userMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Model\\FrontendUser');
        $userMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($expected));

        $repositoryMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Repository\\FrontendUserRepository', array(), array(), '', FALSE);
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $this->fixture->injectUserRepository($repositoryMock);

        $this->assertTrue(
            $this->fixture->isValid($expected)
        );
    }

    /**
     * @test
     * @return void
     */
    public function loggedinUserFoundInDbHasEqualMd5EncryptedPassword()
    {
        $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'] = 'md5';

        $this->fixture->_set('settings', $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);

        $expected = 'myFancyPassword';

        $userId = $this->testingFramework->createAndLoginFrontEndUser('', array('password' => $expected));

        $userMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Model\\FrontendUser');
        $userMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue(md5($expected)));

        $repositoryMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Repository\\FrontendUserRepository', array(), array(), '', FALSE);
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $this->fixture->injectUserRepository($repositoryMock);

        $this->assertTrue(
            $this->fixture->isValid($expected)
        );
    }
}
