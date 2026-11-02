<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Services;

use Evoweb\SfRegister\Domain\Model\FrontendUser as FrontendUserModel;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\Page\PageInformation;

class FrontendUserTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();
    }

    protected function createExtbaseRequest(?FrontendUserModel $userArgument = null): ExtbaseRequest
    {
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName('create');
        if ($userArgument !== null) {
            $extbaseAttribute->setArgument('user', $userArgument);
        }

        return new ExtbaseRequest($this->request->withAttribute('extbase', $extbaseAttribute));
    }

    /**
     * Builds a UriBuilder test double that skips real frontend link resolution (which would require
     * a full site/TSFE setup unrelated to the logic under test) while still recording the arguments
     * autoLogin() passes to it, so the redirect page id and staged login parameters can be asserted.
     *
     * @param array<string, string> $capturedParameter
     */
    protected function getMockedUriBuilder(int &$capturedTargetPageUid, array &$capturedParameter): UriBuilder
    {
        $uriBuilder = $this->createMock(UriBuilder::class);
        $uriBuilder->method('reset')->willReturnSelf();
        $uriBuilder->method('setRequest')->willReturnSelf();
        $uriBuilder->method('setTargetPageUid')
            ->willReturnCallback(function (int $pageUid) use ($uriBuilder, &$capturedTargetPageUid) {
                $capturedTargetPageUid = $pageUid;
                return $uriBuilder;
            });
        $uriBuilder->method('setLinkAccessRestrictedPages')->willReturnSelf();
        $uriBuilder->method('setCreateAbsoluteUri')->willReturnSelf();
        $uriBuilder->method('setArguments')
            ->willReturnCallback(function (array $parameter) use ($uriBuilder, &$capturedParameter) {
                $capturedParameter = $parameter;
                return $uriBuilder;
            });
        $uriBuilder->method('build')->willReturn('https://typo3-testing.local/redirect-target');

        return $uriBuilder;
    }

    protected function getSubject(): FrontendUserService
    {
        /** @var FrontendUserService $subject */
        $subject = $this->get(FrontendUserService::class);
        return $subject;
    }

    protected function getSubjectWithMockedUriBuilder(UriBuilder $uriBuilder): FrontendUserService
    {
        $subject = $this->getSubject();
        $this->getPrivateProperty($subject, 'uriBuilder')->setValue($subject, $uriBuilder);
        return $subject;
    }

    // -- getLoggedInRequestUser --------------------------------------------------------------

    #[Test]
    public function getLoggedInRequestUserReturnsNullWhenNoUserIsLoggedIn(): void
    {
        $this->createEmptyFrontendUser();
        $request = $this->createExtbaseRequest();

        $subject = $this->getSubject();

        self::assertNull($subject->getLoggedInRequestUser($request));
    }

    #[Test]
    public function getLoggedInRequestUserReturnsRepositoryUserWhenLoggedInWithoutUserArgument(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');
        $request = $this->createExtbaseRequest();

        $subject = $this->getSubject();
        $result = $subject->getLoggedInRequestUser($request);

        self::assertInstanceOf(FrontendUserModel::class, $result);
        self::assertSame(1, $result->getUid());
        self::assertSame('testuser', $result->getUsername());
    }

    #[Test]
    public function getLoggedInRequestUserReturnsSubmittedArgumentWhenItMatchesLoggedInUser(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUserModel $submittedUser */
        $submittedUser = $userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUserModel::class, $submittedUser);

        $request = $this->createExtbaseRequest($submittedUser);

        $subject = $this->getSubject();
        $result = $subject->getLoggedInRequestUser($request);

        self::assertSame($submittedUser, $result);
    }

    #[Test]
    public function getLoggedInRequestUserFallsBackToRepositoryWhenArgumentUidDoesNotMatchLoggedInUser(): void
    {
        $this->loginFrontendUser('testuser', 'TestPa$5');

        $mismatchedUser = new FrontendUserModel();
        $mismatchedUser->_setProperty('uid', 999);

        $request = $this->createExtbaseRequest($mismatchedUser);

        $subject = $this->getSubject();
        $result = $subject->getLoggedInRequestUser($request);

        self::assertInstanceOf(FrontendUserModel::class, $result);
        self::assertNotSame($mismatchedUser, $result);
        self::assertSame(1, $result->getUid());
        self::assertSame('testuser', $result->getUsername());
    }

    // -- autoLogin ----------------------------------------------------------------------------

    #[Test]
    public function autoLoginWithValidUserStagesRealUserIdAndThrowsPropagateResponseException(): void
    {
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUserModel $user */
        $user = $userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUserModel::class, $user);

        $capturedTargetPageUid = 0;
        /** @var array<string, string> $capturedParameter */
        $capturedParameter = [];
        $subject = $this->getSubjectWithMockedUriBuilder(
            $this->getMockedUriBuilder($capturedTargetPageUid, $capturedParameter)
        );

        $request = $this->createExtbaseRequest();

        $exception = null;
        try {
            $subject->autoLogin($request, $user, 1);
        } catch (PropagateResponseException $exception) {
        }

        self::assertInstanceOf(PropagateResponseException::class, $exception);
        $response = $exception->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(303, $response->getStatusCode());
        self::assertSame('https://typo3-testing.local/redirect-target', $response->getHeaderLine('Location'));

        self::assertSame(1, $capturedTargetPageUid);
        self::assertSame('login', $capturedParameter['logintype']);
        self::assertArrayHasKey(FrontendUserService::SESSION_KEY, $capturedParameter);

        $hmac = (string)$capturedParameter[FrontendUserService::SESSION_KEY];
        /** @var Registry $registry */
        $registry = $this->get(Registry::class);
        self::assertSame(1, $registry->get('sf-register', $hmac));
    }

    #[Test]
    public function autoLoginWithUnpersistedUserStagesNoRealUserIdSoNoLoginCanFollow(): void
    {
        $user = new FrontendUserModel();

        $capturedTargetPageUid = 0;
        /** @var array<string, string> $capturedParameter */
        $capturedParameter = [];
        $subject = $this->getSubjectWithMockedUriBuilder(
            $this->getMockedUriBuilder($capturedTargetPageUid, $capturedParameter)
        );

        $request = $this->createExtbaseRequest();

        $exception = null;
        try {
            $subject->autoLogin($request, $user, 1);
        } catch (PropagateResponseException $exception) {
        }

        self::assertInstanceOf(PropagateResponseException::class, $exception);
        self::assertArrayHasKey(FrontendUserService::SESSION_KEY, $capturedParameter);

        $hmac = (string)$capturedParameter[FrontendUserService::SESSION_KEY];
        /** @var Registry $registry */
        $registry = $this->get(Registry::class);

        // No real user uid was staged (null instead of an int), so the deferred AutoLogin
        // authentication service can never resolve this to an actual user record.
        self::assertNull($registry->get('sf-register', $hmac));
    }

    #[Test]
    public function autoLoginPrefersUsergroupFeloginRedirectPidOverGivenRedirectPageId(): void
    {
        $userGroup = new FrontendUserGroup();
        $userGroup->setFeloginRedirectPid(2);
        /** @var ObjectStorage<FrontendUserGroup> $userGroups */
        $userGroups = new ObjectStorage();
        $userGroups->attach($userGroup);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUserModel $user */
        $user = $userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUserModel::class, $user);
        $user->setUsergroup($userGroups);

        $capturedTargetPageUid = 0;
        /** @var array<string, string> $capturedParameter */
        $capturedParameter = [];
        $subject = $this->getSubjectWithMockedUriBuilder(
            $this->getMockedUriBuilder($capturedTargetPageUid, $capturedParameter)
        );

        $request = $this->createExtbaseRequest();

        $exception = null;
        try {
            $subject->autoLogin($request, $user, 1);
        } catch (PropagateResponseException $exception) {
        }

        self::assertInstanceOf(PropagateResponseException::class, $exception);
        self::assertSame(2, $capturedTargetPageUid);
    }

    #[Test]
    public function autoLoginFallsBackToCurrentPageWhenRedirectPageIdIsZero(): void
    {
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUserModel $user */
        $user = $userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUserModel::class, $user);

        $capturedTargetPageUid = 0;
        /** @var array<string, string> $capturedParameter */
        $capturedParameter = [];
        $subject = $this->getSubjectWithMockedUriBuilder(
            $this->getMockedUriBuilder($capturedTargetPageUid, $capturedParameter)
        );

        $pageInformation = new PageInformation();
        $pageInformation->setId(1);
        $this->request = $this->request->withAttribute('frontend.page.information', $pageInformation);

        $request = $this->createExtbaseRequest();

        $exception = null;
        try {
            $subject->autoLogin($request, $user, 0);
        } catch (PropagateResponseException $exception) {
        }

        self::assertInstanceOf(PropagateResponseException::class, $exception);
        self::assertSame(1, $capturedTargetPageUid);
    }
}
