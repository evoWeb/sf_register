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
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\Page\PageInformation;

class FrontendUser
{
    public const SESSION_KEY = 'sf-register-user';
    public const ADDITIONAL_SECRET = 'sf-register-autologin';

    public function __construct(
        protected Context $context,
        protected FrontendUserRepository $userRepository,
        protected HashService $hashService,
        protected UriBuilder $uriBuilder,
        protected Registry $registry,
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
        /** @var ?FrontendUserModel $user */
        $user = $userId ? $this->userRepository->findByUid($userId) : null;
        return $user;
    }

    /**
     * Determines the frontend user, either if it's already submitted, or by looking up the mail hash code.
     */
    public function determineFrontendUser(
        RequestInterface $request,
        ?FrontendUserModel $user,
        ?string $hash,
    ): ?FrontendUserModel {
        $frontendUser = $user;

        $requestArguments = $request->getArguments();
        if (isset($requestArguments['user']) && $hash !== null) {
            $calculatedHash = $this->hashService->hmac(
                $requestArguments['action'] . '::' . $requestArguments['user'],
                self::ADDITIONAL_SECRET,
            );
            if ($hash === $calculatedHash) {
                /** @var FrontendUserModel $frontendUser */
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
        FrontendUserModel $user,
        int $redirectPageId,
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
            /** @var PageInformation $pageInformation */
            $pageInformation = $request->getAttribute('frontend.page.information');
            $redirectPageId = $pageInformation->getId();
        }

        session_start();

        $_SESSION[self::SESSION_KEY] = $this->hashService->hmac(
            'auto-login::' . $user->getUid(),
            self::ADDITIONAL_SECRET,
        );

        $this->registry->set('sf-register', $_SESSION[self::SESSION_KEY], $user->getUid());

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
        $this->uriBuilder->reset();
        $this->uriBuilder->setRequest($request);

        $uri = $this->uriBuilder
            ->setTargetPageUid($pageId)
            ->setLinkAccessRestrictedPages(true)
            ->setCreateAbsoluteUri(true)
            ->build();

        return $this->getRedirectResponseForUri($uri);
    }

    /**
     * Redirects the web request to another uri.
     *
     * @param string|UriInterface $uri A string representation of a URI
     */
    protected function getRedirectResponseForUri(string|UriInterface $uri): ResponseInterface
    {
        return new RedirectResponse($uri, 303);
    }

    public function getLoggedInRequestUser(RequestInterface $request): ?FrontendUserModel
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
}
