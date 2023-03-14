<?php

namespace Evoweb\SfRegister\EventListener;

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

use Evoweb\SfRegister\Controller\Event\InitializeActionEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class FeuserControllerListener
{
    public function __construct(
        protected Context $context,
        protected UriBuilder $uriBuilder
    ) {
    }

    public function __invoke(InitializeActionEvent $event): void
    {
        if (!$this->userIsLoggedIn()) {
            $redirectEvent = $event->getSettings()['redirectEvent'];

            if ((int)$redirectEvent['page']) {
                $url = $this->uriBuilder->setTargetPageUid((int)$redirectEvent['page'])->build();
                if ($url) {
                    $event->setResponse(new RedirectResponse($url));
                }
            } else {
                $response = new ForwardResponse($redirectEvent['action']);
                $controller = $redirectEvent['controller'] ?? null;
                if ($controller) {
                    $response = $response->withControllerName($controller);
                }
                $event->setResponse($response);
            }
        }
    }

    public function userIsLoggedIn(): bool
    {
        /** @var UserAspect $userAspect */
        $userAspect = $this->context->getAspect('frontend.user');
        return $userAspect->isLoggedIn();
    }
}
