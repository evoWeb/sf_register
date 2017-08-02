<?php

if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('eID') === 'sf_register') {
    /** @var \Evoweb\SfRegister\Controller\AjaxController $ajax */
    $ajax = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Evoweb\SfRegister\Controller\AjaxController::class);
    $ajax->dispatch();
}
