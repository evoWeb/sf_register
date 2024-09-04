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

namespace Evoweb\SfRegister\Services;

use Evoweb\SfRegister\Domain\Model\FrontendUser as FrontendUserModel;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Context\SecurityAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FrontendUser
{
    public const ADDITIONAL_SECRET = 'sf-register-autologin';

    public function __construct(
        protected Context $context,
        protected FrontendUserRepository $userRepository,
    ) {
    }

    public function getLoggedInUserId(): int
    {
        $userId = 0;

        try {
            /** @var UserAspect $userAspect */
            $userAspect = $this->context->getAspect('frontend.user');
            $userId = (int)$userAspect->get('id');
        } catch (AspectNotFoundException | AspectPropertyNotFoundException) {}

        return $userId;
    }

    public function getLoggedInUser(): ?FrontendUserModel
    {
        $userId = $this->getLoggedInUserId();
        return $userId ? $this->userRepository->findByUid($userId) : null;
    }

    /**
     * Determines the frontend user, either if it's already submitted, or by looking up the mail hash code.
     */
    public function determineFrontendUser(
        RequestInterface $request,
        ?FrontendUserModel $user,
        ?string $hash
    ): ?FrontendUser {
        $frontendUser = $user;

        $requestArguments = $request->getArguments();
        if (isset($requestArguments['user']) && $hash !== null) {
            /** @var HashService $hashService */
            $hashService = GeneralUtility::makeInstance(HashService::class);
            $calculatedHash = $hashService->hmac(
                $requestArguments['action'] . '::' . $requestArguments['user'],
                self::ADDITIONAL_SECRET,
            );
            if ($hash === $calculatedHash) {
                /** @var FrontendUser $frontendUser */
                $frontendUser = $this->userRepository->findByUidIgnoringDisabledField((int)$requestArguments['user']);
            }
        }

        return $frontendUser;
    }

    public function userIsLoggedIn(): bool
    {
        $result = false;
        try {
            /** @var UserAspect $userAspect */
            $userAspect = $this->context->getAspect('frontend.user');
            $result = $userAspect->isLoggedIn();
        } catch (\Exception) {
        }
        return $result;
    }

    public function autoLogin(
        RequestInterface $request,
        FrontendUserInterface $user,
        int $redirectPageId
    ): void {
        // get given redirect page id
        $userGroups = $user->getUsergroup();
        /** @var FrontendUserGroup $userGroup */
        foreach ($userGroups as $userGroup) {
            if ($userGroup->getFeloginRedirectPid()) {
                $redirectPageId = $userGroup->getFeloginRedirectPid();
                break;
            }
        }

        // if redirect is empty set it to current page
        if ($redirectPageId == 0) {
            // @extensionScannerIgnoreLine
            $redirectPageId = $this->getTypoScriptFrontendController($request)->id;
        }

        session_start();

        /** @var HashService $hashService */
        $hashService = GeneralUtility::makeInstance(HashService::class);
        $_SESSION['sf-register-user'] = $hashService->hmac(
            'auto-login::' . $user->getUid(),
            self::ADDITIONAL_SECRET,
        );

        /** @var Registry $registry */
        $registry = GeneralUtility::makeInstance(Registry::class);
        $registry->set('sf-register', $_SESSION['sf-register-user'], $user->getUid());

        if ($redirectPageId > 0) {
            $nonce = SecurityAspect::provideIn($this->context)->provideNonce();

            $parameter = [
                'logintype' => 'login',
                RequestToken::PARAM_NAME => RequestToken::create('core/user-auth/fe')->toHashSignedJwt($nonce),
            ];

            $response = $this->redirectToPage($request, $redirectPageId);
            $response = $response
                ->withHeader(
                    'location',
                    $response->getHeaderLine('location') . '?' . http_build_query($parameter),
                );
            throw new PropagateResponseException($response);
        }
    }

    public function redirectToPage(RequestInterface $request, int $pageId): ResponseInterface
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($request);
        $uriBuilder->reset();

        $uri = $uriBuilder
            ->setTargetPageUid($pageId)
            ->setLinkAccessRestrictedPages(true)
            ->build();

        return $this->redirectToUri($uri);
    }

    /**
     * Redirects the web request to another uri.
     *
     * @param string|UriInterface $uri A string representation of a URI
     */
    protected function redirectToUri(string|UriInterface $uri): ResponseInterface
    {
        $uri = $this->addBaseUriIfNecessary((string)$uri);
        return new RedirectResponse($uri, 303);
    }

    /**
     * Adds the base uri if not already in place.
     *
     * @internal
     */
    protected function addBaseUriIfNecessary(string $uri): string
    {
        return GeneralUtility::locationHeaderUrl($uri);
    }

    public function getLoggedInRequestUser(RequestInterface $request): ?FrontendUserInterface
    {
        $user = null;
        $userId = $this->getLoggedInUserId();
        $originalRequest = $request->getAttribute('extbase')->getOriginalRequest();
        if (
            (
                $request->hasArgument('user')
                || ($originalRequest !== null && $originalRequest->hasArgument('user'))
            )
            && $this->userIsLoggedIn()
        ) {
            /** @var FrontendUserModel $userData */
            $userData = $request->hasArgument('user')
                ? $request->getArgument('user')
                : $originalRequest->getArgument('user');
            if ($userData instanceof FrontendUserModel && $userData->getUid() == $userId) {
                $user = $userData;
            }
        }

        if ($user === null) {
            $userId = $this->getLoggedInUserId();
            /** @var FrontendUserModel $user */
            $user = $this->userRepository->findByIdentifier($userId);
        }

        return $user;
    }

    protected function getTypoScriptFrontendController(RequestInterface $request): ?TypoScriptFrontendController
    {
        return $request->getAttribute('frontend.controller');
    }
}
