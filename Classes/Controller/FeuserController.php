<?php

namespace Evoweb\SfRegister\Controller;

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

use Doctrine\Common\Annotations\DocParser;
use Evoweb\SfRegister\Controller\Event\InitializeActionEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter;
use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Validation\Validator\SettableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\ReferringRequest;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * An frontend user controller
 */
class FeuserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var string
     */
    protected $controller = '';

    /**
     * @var array
     */
    protected $ignoredActions = [];

    /**
     * @var \TYPO3\CMS\Core\Context\Context
     */
    protected $context;

    /**
     * User repository
     *
     * @var FrontendUserRepository
     */
    protected $userRepository;

    /**
     * Usergroup repository
     *
     * @var FrontendUserGroupRepository
     */
    protected $userGroupRepository;

    /**
     * File service
     *
     * @var \Evoweb\SfRegister\Services\File
     */
    protected $fileService;

    /**
     * The current view, as resolved by resolveView()
     *
     * @var \TYPO3\CMS\Fluid\View\TemplateView
     * @api
     */
    protected $view;

    /**
     * The current request.
     *
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Request
     * @api
     */
    protected $request;

    /**
     * Active if autologgin was set.
     *
     * Used to define of on page redirect an additional
     * query parameter should be set.
     *
     * @var bool
     */
    protected $autoLoginTriggered = false;

    public function __construct(
        \TYPO3\CMS\Core\Context\Context $context,
        \Evoweb\SfRegister\Services\File $fileService = null,
        FrontendUserRepository $userRepository = null,
        FrontendUserGroupRepository $userGroupRepository = null
    ) {
        $this->context = $context;

        $this->fileService = $fileService;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
    }


    /**
     * Disable flash messages
     *
     * @return bool
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    protected function initializeActionMethodValidators()
    {
        if (!is_array($this->settings['fields']['selected'])) {
            $this->settings['fields']['selected'] = explode(',', $this->settings['fields']['selected']);
        }

        if ($this->arguments->hasArgument('user') && !$this->actionIsIgnored()) {
            $this->modifyValidatorsBasedOnSettings(
                $this->arguments->getArgument('user'),
                $this->settings['validation'][$this->controller] ?? []
            );
        } elseif ($this->arguments->hasArgument('password') && !$this->actionIsIgnored()) {
            $this->modifyValidatorsBasedOnSettings(
                $this->arguments->getArgument('password'),
                $this->settings['validation']['password'] ?? []
            );
        } elseif ($this->arguments->hasArgument('email') && !$this->actionIsIgnored()) {
            $this->modifyValidatorsBasedOnSettings(
                $this->arguments->getArgument('email'),
                $this->settings['validation'][$this->controller] ?? []
            );
        } else {
            parent::initializeActionMethodValidators();
        }
    }

    protected function actionIsIgnored(): bool
    {
        if (
            isset($this->settings['ignoredActions'][$this->controller])
            && is_array($this->settings['ignoredActions'][$this->controller])
        ) {
            $this->ignoredActions = array_merge(
                $this->settings['ignoredActions'][$this->controller],
                $this->ignoredActions
            );
        }
        return in_array($this->actionMethodName, $this->ignoredActions);
    }

    protected function modifyValidatorsBasedOnSettings(
        \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument,
        array $configuredValidators
    ) {
        $parser = new DocParser();

        /** @var \Evoweb\SfRegister\Validation\Validator\UserValidator $validator */
        $validator = GeneralUtility::makeInstance(\Evoweb\SfRegister\Validation\Validator\UserValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (!in_array($fieldName, $this->settings['fields']['selected'])) {
                continue;
            }

            if (!is_array($configuredValidator)) {
                $validatorInstance = $this->getValidatorByConfiguration(
                    $configuredValidator,
                    $parser
                );

                if ($validatorInstance instanceof SettableInterface) {
                    $validatorInstance->setPropertyName($fieldName);
                }
            } else {
                $validatorInstance = GeneralUtility::makeInstance(
                    \Evoweb\SfRegister\Validation\Validator\ConjunctionValidator::class
                );
                foreach ($configuredValidator as $individualConfiguredValidator) {
                    $individualValidatorInstance = $this->getValidatorByConfiguration(
                        $individualConfiguredValidator,
                        $parser
                    );

                    if ($individualValidatorInstance instanceof SettableInterface) {
                        $individualValidatorInstance->setPropertyName($fieldName);
                    }

                    $validatorInstance->addValidator($individualValidatorInstance);
                }
            }

            $validator->addPropertyValidator($fieldName, $validatorInstance);
        }

        if (in_array($this->controller, ['edit', 'delete'])) {
            $validatorName = \Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator::class;
        } else {
            $validatorName = \Evoweb\SfRegister\Validation\Validator\EmptyValidator::class;
        }
        $validatorInstance = $this->getValidatorByConfiguration($validatorName, $parser);
        $validator->addPropertyValidator('uid', $validatorInstance);

        $argument->setValidator($validator);
    }

    /**
     * @param string $configuration
     * @param \Doctrine\Common\Annotations\DocParser $parser
     *
     * @return \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface
     */
    protected function getValidatorByConfiguration(string $configuration, DocParser $parser)
    {
        if (strpos($configuration, '"') === false && strpos($configuration, '(') === false) {
            $configuration = '"' . $configuration . '"';
        }

        /** @var \TYPO3\CMS\Extbase\Annotation\Validate $validateAnnotation */
        $validateAnnotation = current($parser->parse(
            '@TYPO3\CMS\Extbase\Annotation\Validate(' . $configuration . ')'
        ));
        $validatorObjectName = \TYPO3\CMS\Extbase\Validation\ValidatorClassNameResolver::resolve(
            $validateAnnotation->validator
        );

        /** @var \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface $validator */
        $validator = $this->objectManager->get($validatorObjectName, $validateAnnotation->options);
        return $validator;
    }

    protected function initializeAction()
    {
        $this->setTypeConverter();

        if ($this->settings['processInitializeActionEvent']) {
            $this->eventDispatcher->dispatch(new InitializeActionEvent($this, $this->settings));
        }

        if (
            $this->request->getControllerActionName() != 'removeImage'
            && $this->request->hasArgument('removeImage')
            && $this->request->getArgument('removeImage')
        ) {
            $this->forward('removeImage');
        }
    }

    protected function setTypeConverter()
    {
        $argumentName = 'user';
        if ($this->request->hasArgument($argumentName)) {
            /** @var PropertyMappingConfiguration $configuration */
            $configuration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
            /** @var array $user */
            $user = $this->request->getArgument('user');

            $this->getPropertyMappingConfiguration(
                $configuration,
                $user
            );
        }
    }

    /**
     * @param PropertyMappingConfiguration|null $configuration
     * @param array $userData
     *
     * @return PropertyMappingConfiguration
     */
    protected function getPropertyMappingConfiguration(
        PropertyMappingConfiguration $configuration = null,
        $userData = []
    ): PropertyMappingConfiguration {
        if (is_null($configuration)) {
            $configuration = GeneralUtility::makeInstance(PropertyMappingConfiguration::class);
        }

        $configuration->allowAllProperties();
        $configuration->forProperty('usergroup')->allowAllProperties();
        $configuration->forProperty('moduleSysDmailCategory')->allowAllProperties();
        $configuration->forProperty('image')->allowAllProperties();
        $configuration->setTypeConverterOption(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
            true
        );

        $folder = $this->fileService->getTempFolder();
        $uploadConfiguration = [
            UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS =>
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER =>
                $folder->getStorage()->getUid() . ':' . $folder->getIdentifier(),
        ];

        $configuration->forProperty('image.0')
            ->setTypeConverterOptions(
                UploadedFileReferenceConverter::class,
                $uploadConfiguration
            );

        $configuration->forProperty('dateOfBirth')
            ->setTypeConverterOptions(
                DateTimeConverter::class,
                [
                    DateTimeConverter::CONFIGURATION_USER_DATA => $userData,
                ]
            );

        return $configuration;
    }

    /**
     * Initialize an view object to be able to set templateRootPath from flex form
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        if (isset($this->settings['templateRootPath']) && !empty($this->settings['templateRootPath'])) {
            $templateRootPath = GeneralUtility::getFileAbsFileName($this->settings['templateRootPath']);
            if (GeneralUtility::isAllowedAbsPath($templateRootPath)) {
                $templateRootPaths = $this->view->getTemplateRootPaths() ?? [];
                $this->view->setTemplateRootPaths(array_merge($templateRootPaths, [$templateRootPath]));
            }
        }
    }


    /**
     * Proxy action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function proxyAction(
        \Evoweb\SfRegister\Domain\Model\FrontendUser $user
    ) {
        $action = 'save';

        if ($this->request->hasArgument('form')) {
            $action = 'form';
        }

        $this->forward($action, null, null, ['user' => $user]);
    }

    /**
     * Remove an image and forward to the action where it was called
     *
     * @param FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation $user
     */
    protected function removeImageAction(FrontendUser $user)
    {
        /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $image */
        $image = $user->getImage()->current();

        $this->fileService->removeFile($image);
        $this->removeImageFromUserAndRequest($user);

        $this->request->setArgument('removeImage', false);

        $referringRequest = null;
        $referringRequestArguments = $this->request->getInternalArguments()['__referrer']['@request'] ?? null;
        if (is_string($referringRequestArguments)) {
            /** @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService */
            $hashService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Security\Cryptography\HashService::class);
            $referrerArray = json_decode(
                $hashService->validateAndStripHmac($referringRequestArguments),
                true
            );
            $arguments = [];
            $referringRequest = new ReferringRequest();
            $referringRequest->setArguments(array_replace_recursive($arguments, $referrerArray));
        }

        if ($referringRequest !== null) {
            $this->forward(
                $referringRequest->getControllerActionName(),
                $referringRequest->getControllerName(),
                $referringRequest->getControllerExtensionName(),
                $this->request->getArguments()
            );
        }
    }

    protected function removeImageFromUserAndRequest(FrontendUser $user): FrontendUser
    {
        if ($user->getUid() !== null) {
            /** @var FrontendUser $localUser */
            $localUser = $this->userRepository->findByUid($user->getUid());
            $localUser->removeImage();
            $this->userRepository->update($localUser);

            $this->persistAll();
        }

        $user->removeImage();

        /** @var array $requestUser */
        $requestUser = $this->request->getArgument('user');
        if (is_array($requestUser)) {
            $requestUser['image'] = $user->getImage();
        }
        $this->request->setArgument('user', $requestUser);

        return $user;
    }

    public function encryptPassword(string $password): string
    {
        /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $passwordHashFactory */
        $passwordHashFactory = GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class
        );
        $passwordHash = $passwordHashFactory->getDefaultHashInstance('FE');
        return $passwordHash->getHashedPassword($password);
    }

    protected function persistAll()
    {
        GeneralUtility::getContainer()
            ->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class)->persistAll();
    }

    protected function redirectToPage(int $pageId)
    {
        $this->uriBuilder->reset();
        if ($this->autoLoginTriggered) {
            $statusField = $this->getTypoScriptFrontendController()->fe_user->formfield_permanent;
            $this->uriBuilder->setArguments([
                'logintype' => 'login',
                $statusField => 'login'
            ]);
        }

        $url = $this->uriBuilder
            ->setTargetPageUid($pageId)
            ->build();

        $this->redirectToUri($url);
    }


    protected function sendEmails(FrontendUser $user, string $action): FrontendUser
    {
        $action = ucfirst(str_replace('Action', '', $action));
        $controller = str_replace(
            ['Evoweb\\SfRegister\\Controller\\Feuser', 'Controller'],
            '',
            static::class
        );

        $type = $controller . $action;

        /** @var \Evoweb\SfRegister\Services\Mail $mailService */
        $mailService = GeneralUtility::getContainer()->get(\Evoweb\SfRegister\Services\Mail::class);

        if ($this->isNotifyAdmin($type)) {
            $user = $mailService->sendNotifyAdmin($user, $controller, $action);
        }

        if ($this->isNotifyUser($type)) {
            $user = $mailService->sendNotifyUser($user, $controller, $action);
        }

        return $user;
    }

    protected function isNotifyAdmin(string $type): bool
    {
        $type = lcfirst($type);
        $notifySettings = $this->settings['notifyAdmin'];
        return isset($notifySettings[$type]) && !empty($notifySettings[$type]);
    }

    protected function isNotifyUser(string $type): bool
    {
        $type = lcfirst($type);
        $notifySettings = $this->settings['notifyUser'];
        return isset($notifySettings[$type]) && !empty($notifySettings[$type]);
    }


    /**
     * Determines whether a user is in a given user group.
     *
     * @param FrontendUser $user
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUserGroup|string|int $userGroup
     *
     * @return bool
     */
    protected function isUserInUserGroup(FrontendUser $user, $userGroup): bool
    {
        $return = false;

        if ($userGroup instanceof \Evoweb\SfRegister\Domain\Model\FrontendUserGroup) {
            $return = $user->getUsergroup()->contains($userGroup);
        } elseif (!empty($userGroup)) {
            $userGroupUids = $this->getEntityUids(
                $user->getUsergroup()->toArray()
            );

            $return = in_array($userGroup, $userGroupUids);
        }

        return $return;
    }

    /**
     * Determines whether a user is in a given user group.
     *
     * @param FrontendUser $user
     * @param array|\Evoweb\SfRegister\Domain\Model\FrontendUserGroup[] $userGroups
     *
     * @return bool
     */
    protected function isUserInUserGroups(FrontendUser $user, array $userGroups): bool
    {
        $return = false;

        foreach ($userGroups as $userGroup) {
            if ($this->isUserInUserGroup($user, $userGroup)) {
                $return = true;
            }
        }

        return $return;
    }

    protected function getFollowingUserGroups(int $currentUserGroup): array
    {
        $userGroups = $this->getUserGroupIds();
        $currentIndex = array_search((int)$currentUserGroup, array_values($userGroups));

        $reducedUserGroups = [];
        if ($currentIndex !== false && $currentIndex < count($userGroups)) {
            $reducedUserGroups = array_slice($userGroups, $currentIndex);
        }

        return $reducedUserGroups;
    }

    protected function getUserGroupIds(): array
    {
        $settingsUserGroupKeys = $this->getUserGroupIdSettingKeys();

        $userGroups = [];
        foreach ($settingsUserGroupKeys as $settingsUserGroupKey) {
            $userGroup = (int) $this->settings[$settingsUserGroupKey];
            if ($userGroup) {
                $userGroups[$settingsUserGroupKey] = $userGroup;
            }
        }

        return $userGroups;
    }

    /**
     * Gets the uid of each given entity.
     *
     * @param array|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity[] $entities
     *
     * @return array
     */
    protected function getEntityUids(array $entities): array
    {
        $entityUids = [];

        foreach ($entities as $entity) {
            $entityUids[] = $entity->getUid();
        }

        return $entityUids;
    }


    protected function changeUsergroup(FrontendUser $user, int $userGroupIdToAdd): FrontendUser
    {
        $this->removePreviousUserGroups($user);

        $userGroupIdToAdd = (int) $userGroupIdToAdd;
        if ($userGroupIdToAdd) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUserGroup $userGroupToAdd */
            $userGroupToAdd = $this->userGroupRepository->findByUid($userGroupIdToAdd);
            $user->addUsergroup($userGroupToAdd);
        }

        return $user;
    }

    protected function removePreviousUserGroups(FrontendUser $user)
    {
        $userGroupIds = $this->getUserGroupIds();
        $assignedUserGroups = $user->getUsergroup();
        foreach ($assignedUserGroups as $singleUserGroup) {
            if (in_array($singleUserGroup->getUid(), $userGroupIds)) {
                $assignedUserGroups->detach($singleUserGroup);
            }
        }
        $user->setUsergroup($assignedUserGroups);
    }

    protected function moveTemporaryImage(FrontendUser $user)
    {
        if ($user->getImage()->count()) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $image */
            $image = $user->getImage()->current();
            $this->fileService->moveFileFromTempFolderToUploadFolder($image);
        }
    }


    protected function autoLogin(FrontendUser $user, int $redirectPageId)
    {
        session_start();
        $this->autoLoginTriggered = true;

        $_SESSION['sf-register-user'] = GeneralUtility::hmac('auto-login::' . $user->getUid(), $GLOBALS['EXEC_TIME']);

        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);
        $registry->set('sf-register', $_SESSION['sf-register-user'], $user->getUid());

        // if redirect was empty by now set it to current page
        if (intval($redirectPageId) == 0) {
            $redirectPageId = $this->getTypoScriptFrontendController()->id;
        }

        // get configured redirect page id if given
        $userGroups = $user->getUsergroup();
        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUserGroup $userGroup */
        foreach ($userGroups as $userGroup) {
            if ($userGroup->getFeloginRedirectPid()) {
                $redirectPageId = $userGroup->getFeloginRedirectPid();
                break;
            }
        }

        if ($redirectPageId > 0) {
            $this->redirectToPage((int) $redirectPageId);
        }
    }

    protected function userIsLoggedIn(): bool
    {
        /** @var \TYPO3\CMS\Core\Context\UserAspect $userAspect */
        $userAspect = $this->context->getAspect('frontend.user');
        return $userAspect->isLoggedIn();
    }

    /**
     * Determines the frontend user, either if it's
     * already submitted, or by looking up the mail hash code.
     *
     * @param NULL|FrontendUser $user
     * @param NULL|string $hash
     *
     * @return NULL|FrontendUser
     */
    protected function determineFrontendUser(FrontendUser $user = null, string $hash = null)
    {
        $frontendUser = null;

        $requestArguments = $this->request->getArguments();
        if ($user !== null && $hash !== null) {
            $calculatedHash = GeneralUtility::hmac(
                $requestArguments['action'] . '::' . $user->getUid()
            );
            if ($hash === $calculatedHash) {
                $frontendUser = $user;
            }
        }

        return $frontendUser;
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Return the keys of the TypoScript configuration in the order which is relevant for the configured
     * registration workflow
     *
     * @return array
     */
    protected function getUserGroupIdSettingKeys(): array
    {
        $defaultOrder = [
            'usergroup',
            'usergroupPostSave',
            'usergroupPostConfirm',
            'usergroupPostAccept',
        ];

        // Admin    [plugin.tx_sfregister.settings.acceptEmailPostCreate]
        $confirmEmailPostCreate = (bool)$this->settings['confirmEmailPostCreate'];
        // User     [plugin.tx_sfregister.settings.confirmEmailPostAccept]
        $acceptEmailPostCreate = (bool)$this->settings['acceptEmailPostCreate'];
        // Admin    [plugin.tx_sfregister.settings.acceptEmailPostConfirm]
        $confirmEmailPostAccept = (bool)$this->settings['confirmEmailPostAccept'];
        // User     [plugin.tx_sfregister.settings.confirmEmailPostCreate]
        $acceptEmailPostConfirm = (bool)$this->settings['acceptEmailPostConfirm'];

        // First User:confirm then Admin:accept
        if ($confirmEmailPostCreate && $acceptEmailPostConfirm) {
            return $defaultOrder;
        }

        // First Admin:accept then User:confirm
        if ($acceptEmailPostCreate && $confirmEmailPostAccept) {
            return [
                'usergroup',
                'usergroupPostSave',
                'usergroupPostAccept',
                'usergroupPostConfirm',
            ];
        }

        return $defaultOrder;
    }
}
