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

use Evoweb\SfRegister\Controller\Event\ProcessInitializeActionEvent;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class FeuserControllerListener
{
    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    public function __construct(UriBuilder $uriBuilder)
    {
        $this->uriBuilder = $uriBuilder;
    }

    public function onProcessInitializeActionEvent(ProcessInitializeActionEvent $event)
    {
        if (!$this->userIsLoggedIn()) {
            $redirectSettings = $event->getSettings()['redirectSignal'];

            if ((int) $redirectSettings['page']) {
                $this->redirectToPage((int) $redirectSettings['page']);
            } elseif ($redirectSettings['controller']) {
                $event->getController()->forward($redirectSettings['action'], $redirectSettings['controller']);
            } else {
                $event->getController()->forward($redirectSettings['action']);
            }
        }
    }

    public function userIsLoggedIn(): bool
    {
        return is_array($this->getTypoScriptFrontendController()->fe_user->user);
    }

    protected function redirectToPage(int $pageId)
    {
        $url = $this->uriBuilder->setTargetPageUid($pageId)->build();
        \TYPO3\CMS\Core\Utility\HttpUtility::redirect($url);
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
