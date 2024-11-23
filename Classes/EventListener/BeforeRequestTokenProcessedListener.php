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

namespace Evoweb\SfRegister\EventListener;

use TYPO3\CMS\Core\Authentication\Event\BeforeRequestTokenProcessedEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\SecurityAspect;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

final class BeforeRequestTokenProcessedListener
{
    public function __construct(protected Context $context)
    {
    }

    public function __invoke(BeforeRequestTokenProcessedEvent $event): void
    {
        if ($event->getRequestToken() instanceof RequestToken) {
            // fine, there is a valid request token
            return;
        }
        if (!$event->getUser() instanceof FrontendUserAuthentication) {
            return;
        }

        $queryParams = ($event->getRequest()?->getQueryParams() ?? []);
        $loginType = ($queryParams['logintype'] ?? '');
        $tokenValue = ($queryParams[RequestToken::PARAM_NAME] ?? '');

        if ($loginType === 'login' && $tokenValue !== '') {
            $signingSecretResolver = SecurityAspect::provideIn($this->context)->getSigningSecretResolver();
            try {
                $event->setRequestToken(RequestToken::fromHashSignedJwt($tokenValue, $signingSecretResolver));
            } catch (\Exception) {
            }
        }
    }
}
