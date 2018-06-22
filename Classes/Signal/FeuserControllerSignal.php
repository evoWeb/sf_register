<?php
namespace Evoweb\SfRegister\Signal;

/***************************************************************
* Copyright notice
*
* (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class FeuserControllerSignal
{
    public function initializeAction(\Evoweb\SfRegister\Controller\FeuserController $controller, array $settings)
    {
        if (!$this->userIsLoggedIn()) {
            $redirectSettings = $settings['redirectSignal'];

            if ((int) $redirectSettings['page']) {
                $this->redirectToPage((int) $redirectSettings['page']);
            } elseif ($redirectSettings['controller']) {
                $controller->forward($redirectSettings['action'], $redirectSettings['controller']);
            } else {
                $controller->forward($redirectSettings['action']);
            }
        }
    }

    public function userIsLoggedIn(): bool
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return is_array($this->getTypoScriptFrontendController()->fe_user->user);
    }

    protected function redirectToPage(int $pageId)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
        $url = $uriBuilder->setTargetPageUid($pageId)->build();
        \TYPO3\CMS\Core\Utility\HttpUtility::redirect($url);
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
