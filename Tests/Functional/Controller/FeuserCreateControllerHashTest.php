<?php

namespace Evoweb\SfRegister\Tests\Functional\Controller;

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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\Mock\FeuserCreateController;
use Evoweb\SfRegister\Tests\Functional\RecordingView;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Covers the link hash that guards the confirm, accept, refuse and decline actions
 * (TYPO3-EXT-SA-2026-038). The actions are called directly on a controller built with real
 * collaborators and a recording view, so no frontend request or rendered template is needed.
 */
class FeuserCreateControllerHashTest extends AbstractTestBase
{
    /**
     * Builds the link hash that Link\ActionViewHelper adds to the mailed confirm, accept, refuse
     * and decline links, and that FeuserController::determineFrontendUser() validates before it
     * resolves the user. Actions relying on that hash can only be tested with a request that
     * carries the "action" and "user" arguments the hash is calculated from.
     */
    protected function createLinkHash(string $action, int $userUid): string
    {
        return GeneralUtility::hmac($action . '::' . $userUid);
    }

    /**
     * Builds a FeuserCreateController wired with real collaborators (except the repository,
     * which the caller supplies so it can assert on update()/remove()) and a request carrying
     * the given action/user arguments, ready to have an action method called on it directly.
     */
    protected function getSubjectForFailClosedTest(
        string $action,
        int $userUid,
        FrontendUserRepository $userRepository
    ): FeuserCreateController {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $subject = new FeuserCreateController($context, $file, $userRepository, $userGroupRepository);

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $subject->injectEventDispatcher($eventDispatcher);

        $extbaseParameters = new ExtbaseRequestParameters(FeuserCreateController::class);
        $extbaseParameters->setArguments(['action' => $action, 'user' => $userUid]);
        $request = new Request((new ServerRequest())->withAttribute('extbase', $extbaseParameters));
        $subject->set('request', $request);
        $subject->set('settings', []);

        $subject->set('view', new RecordingView());

        return $subject;
    }

    protected function mockRepositoryUpdateExpectation(FrontendUser $expectedUser): FrontendUserRepository&MockObject
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'findByUidIgnoringDisabledField'])
            ->getMock();
        $repository->expects(self::once())->method('update')->with(self::identicalTo($expectedUser));
        $repository->method('findByUidIgnoringDisabledField')
            ->with($expectedUser->getUid())
            ->willReturn($expectedUser);

        return $repository;
    }

    protected function mockRepositoryRemoveExpectation(FrontendUser $expectedUser): FrontendUserRepository&MockObject
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove', 'findByUidIgnoringDisabledField'])
            ->getMock();
        $repository->expects(self::once())->method('remove')->with(self::identicalTo($expectedUser));
        $repository->method('findByUidIgnoringDisabledField')
            ->with($expectedUser->getUid())
            ->willReturn($expectedUser);

        return $repository;
    }

    /**
     * Counterpart for the fail closed case. Neither the lookup in determineFrontendUser() nor
     * remove()/update() may be reached when the link hash does not validate.
     */
    protected function mockRepositoryWithoutWriteExpectation(): FrontendUserRepository&MockObject
    {
        /** @var FrontendUserRepository&MockObject $repository */
        $repository = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'remove', 'findByUidIgnoringDisabledField'])
            ->getMock();
        $repository->expects(self::never())->method('update');
        $repository->expects(self::never())->method('remove');
        $repository->expects(self::never())->method('findByUidIgnoringDisabledField');

        return $repository;
    }

    #[Test]
    public function refuseActionRemovesTheTargetedUserWhenTheHashIsValid(): void
    {
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);

        $repository = $this->mockRepositoryRemoveExpectation($user);
        $subject = $this->getSubjectForFailClosedTest('refuse', 1, $repository);

        $response = $subject->refuseAction($user, $this->createLinkHash('refuse', 1));

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertSame(1, $view->variables['userRefused']);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function refuseActionLeavesTheTargetedUserUntouchedWhenTheHashIsMissing(): void
    {
        // An attacker who only knows (or guesses) a victim uid and omits the hash entirely
        // sends exactly the request shape that TYPO3-EXT-SA-2026-038 allowed through.
        $attackerSuppliedUser = clone new FrontendUser();
        $attackerSuppliedUser->_setProperty('uid', 1);

        $repository = $this->mockRepositoryWithoutWriteExpectation();
        $subject = $this->getSubjectForFailClosedTest('refuse', 1, $repository);

        $response = $subject->refuseAction($attackerSuppliedUser, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertArrayNotHasKey('userRefused', $view->variables);
        self::assertArrayNotHasKey('user', $view->variables);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function refuseActionLeavesTheTargetedUserUntouchedWhenTheHashIsWrong(): void
    {
        $attackerSuppliedUser = clone new FrontendUser();
        $attackerSuppliedUser->_setProperty('uid', 1);

        $repository = $this->mockRepositoryWithoutWriteExpectation();
        $subject = $this->getSubjectForFailClosedTest('refuse', 1, $repository);

        $response = $subject->refuseAction($attackerSuppliedUser, 'wrong-hash');

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertArrayNotHasKey('userRefused', $view->variables);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function confirmActionActivatesTheTargetedUserWhenTheHashIsValid(): void
    {
        $user = clone new FrontendUser();
        $user->_setProperty('uid', 1);
        $user->setDisable(true);

        $repository = $this->mockRepositoryUpdateExpectation($user);
        $subject = $this->getSubjectForFailClosedTest('confirm', 1, $repository);

        $subject->confirmAction($user, $this->createLinkHash('confirm', 1));

        self::assertFalse($user->getDisable());
        self::assertNotNull($user->getActivatedOn());
    }

    #[Test]
    public function confirmActionLeavesTheTargetedUserUntouchedWhenTheHashIsMissing(): void
    {
        $attackerSuppliedUser = clone new FrontendUser();
        $attackerSuppliedUser->_setProperty('uid', 1);
        $attackerSuppliedUser->setDisable(true);

        $repository = $this->mockRepositoryWithoutWriteExpectation();
        $subject = $this->getSubjectForFailClosedTest('confirm', 1, $repository);

        $subject->confirmAction($attackerSuppliedUser, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertTrue(
            $attackerSuppliedUser->getDisable(),
            'the attacker-supplied user must not be activated when the hash does not validate'
        );
    }

    #[Test]
    public function confirmActionLeavesTheTargetedUserUntouchedWhenTheHashIsWrong(): void
    {
        $attackerSuppliedUser = clone new FrontendUser();
        $attackerSuppliedUser->_setProperty('uid', 1);
        $attackerSuppliedUser->setDisable(true);

        $repository = $this->mockRepositoryWithoutWriteExpectation();
        $subject = $this->getSubjectForFailClosedTest('confirm', 1, $repository);

        $subject->confirmAction($attackerSuppliedUser, 'wrong-hash');

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userNotFound']);
        self::assertTrue(
            $attackerSuppliedUser->getDisable(),
            'the attacker-supplied user must not be activated when the hash does not validate'
        );
    }
}
