<?php

namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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

/**
 * An frontend user resend controller
 */
class FeuserResendController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'resend';


    public function formAction(\Evoweb\SfRegister\Domain\Model\Email $email = null)
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Request $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ($originalRequest !== null && $originalRequest->hasArgument('email')) {
            /** @var array $email */
            $email = $this->request->hasArgument('email') ?
                $this->request->getArgument('email') : $originalRequest->getArgument('email');

            $propertyMappingConfiguration = $this->getPropertyMappingConfiguration(null, $email);

            /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
            $propertyMapper = $this->objectManager->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
            $email = $propertyMapper->convert(
                $email,
                \Evoweb\SfRegister\Domain\Model\Email::class,
                $propertyMappingConfiguration
            );
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'email' => &$email,
                'settings' => $this->settings,
            ]
        );

        $this->view->assign('email', $email);
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Email $email
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="email")
     */
    public function mailAction(\Evoweb\SfRegister\Domain\Model\Email $email)
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'email' => &$email,
                'settings' => $this->settings
            ]
        );

        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $this->sendEmails($user, 'PostCreateSave');
        }
    }
}
