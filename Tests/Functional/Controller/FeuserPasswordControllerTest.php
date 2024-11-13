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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use EvowebTests\TestClasses\Controller\FeuserPasswordController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserPasswordControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->initializeRequest();
    }

    #[Test]
    public function userIsLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        /** @var FrontendUserService $subject */
        $subject = $this->get(FrontendUserService::class);

        $this->assertFalse($subject->userIsLoggedIn());
    }

    #[Test]
    public function userIsLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var FrontendUserService $subject */
        $subject = $this->get(FrontendUserService::class);

        $this->assertTrue($subject->userIsLoggedIn());
    }

    #[Test]
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository(): void
    {
        $userId = 1;
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var ModifyValidator $modifyValidator */
        $modifyValidator = $this->get(ModifyValidator::class);
        /** @var FileService $fileService */
        $fileService = $this->get(FileService::class);
        /** @var Context $context */
        $context = $this->get(Context::class);

        $expected = 'TestPa$5';
        // we need to clone to create the object, else the isClone parameter is not set and both object wont match
        $frontendUser = clone new FrontendUser();
        $frontendUser->setPassword($expected);

        /** @var FrontendUserRepository|MockObject $frontendUserRepository */
        $frontendUserRepository = $this->getMockBuilder(FrontendUserRepository::class)
            ->onlyMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendUserRepository->expects($this->once())
            ->method('findByUid')
            ->with($this->equalTo($userId))
            ->willReturn($frontendUser);
        $frontendUserRepository->expects($this->once())
            ->method('update')
            ->with($this->equalTo($frontendUser));

        /** @var HashService $hashService */
        $hashService = $this->get(HashService::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->get(UriBuilder::class);
        /** @var Registry $registry */
        $registry = $this->get(Registry::class);

        $frontendUserService = new FrontendUserService(
            $context,
            $frontendUserRepository,
            $hashService,
            $uriBuilder,
            $registry
        );

        $subject = new FeuserPasswordController(
            $modifyValidator,
            $fileService,
            $frontendUserRepository,
            $frontendUserService
        );

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, ['encryptPassword' => '']);

        /** @var FluidViewAdapter $view */
        $view = $this->getMockBuilder(FluidViewAdapter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $view->expects($this->once())
            ->method('render')
            ->willReturn('Password successfully updated');

        $property = $this->getPrivateProperty($subject, 'view');
        $property->setValue($subject, $view);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get(EventDispatcher::class);
        $subject->injectEventDispatcher($eventDispatcher);

        $request = new Request($this->request);
        $request = $request->withAttribute('currentContentObject', new ContentObjectRenderer());
        $subject->set('request', $request);

        $password = new Password();
        $password->_setProperty('password', $expected);
        $response = $subject->saveAction($password);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
