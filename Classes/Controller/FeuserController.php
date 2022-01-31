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
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter;
use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\Mail;
use Evoweb\SfRegister\Validation\Validator\ConjunctionValidator;
use Evoweb\SfRegister\Validation\Validator\EmptyValidator;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator;
use Evoweb\SfRegister\Validation\Validator\InjectableInterface;
use Evoweb\SfRegister\Validation\Validator\SettableInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
// @todo replace usage of forward with ForwardResponse
use TYPO3\CMS\Extbase\Mvc\Web\ReferringRequest;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorClassNameResolver;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * An frontend user controller containing common methods
 */
class FeuserController extends ActionController
{
    protected string $controller = '';

    protected array $ignoredActions = [];

    protected Context $context;

    protected FrontendUserRepository $userRepository;

    protected FrontendUserGroupRepository $userGroupRepository;

    protected File $fileService;

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
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     * @api
     */
    protected $request;

    /**
     * Active if autologin was set.
     *
     * Used to define of on page redirect an additional
     * query parameter should be set.
     */
    protected bool $autoLoginTriggered = false;

    public function __construct(
        Context $context,
        File $fileService,
        FrontendUserRepository $userRepository,
        FrontendUserGroupRepository $userGroupRepository
    ) {
        $this->context = $context;
        $this->fileService = $fileService;
        $this->userRepository = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * Disable flash messages
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    protected function initializeActionMethodValidators()
    {
        $this->settings['hasOriginalRequest'] = $this->request->getOriginalRequest() !== null;

        if (!is_array($this->settings['fields']['selected'])) {
            $this->settings['fields']['selected'] = explode(',', $this->settings['fields']['selected']);
        }

        if ($this->actionIsIgnored()) {
            parent::initializeActionMethodValidators();
        } else {
            $argumentNames = array_intersect(
                array_values($this->arguments->getArgumentNames()),
                ['user', 'password', 'email']
            );

            foreach ($argumentNames as $argument) {
                $this->modifyValidatorsBasedOnSettings(
                    $this->arguments->getArgument($argument),
                    $this->settings['validation'][strtolower($this->controller)] ?? []
                );

            }
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
        Argument $argument,
        array $configuredValidators
    ) {
        $parser = new DocParser();

        /** @var UserValidator $validator */
        $validator = GeneralUtility::makeInstance(UserValidator::class);
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
                    ConjunctionValidator::class
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

        if (in_array($this->controller, ['Edit', 'Delete'])) {
            $validatorName = EqualCurrentUserValidator::class;
        } else {
            $validatorName = EmptyValidator::class;
        }
        $validatorInstance = $this->getValidatorByConfiguration($validatorName, $parser);
        $validator->addPropertyValidator('uid', $validatorInstance);

        $argument->setValidator($validator);
    }

    protected function getValidatorByConfiguration(string $configuration, DocParser $parser): ValidatorInterface
    {
        if (strpos($configuration, '"') === false && strpos($configuration, '(') === false) {
            $configuration = '"' . $configuration . '"';
        }

        /** @var Validate $validateAnnotation */
        $validateAnnotation = current($parser->parse('@' . Validate::class . '(' . $configuration . ')'));
        $validatorObjectName = ValidatorClassNameResolver::resolve($validateAnnotation->validator);

        if (in_array(InjectableInterface::class, class_implements($validatorObjectName))) {
            /** @var ValidatorInterface|InjectableInterface $validator */
            $validator = GeneralUtility::makeInstance($validatorObjectName);
            $validator->setOptions($validateAnnotation->options);
        } else {
            /** @var ValidatorInterface $validator */
            $validator = GeneralUtility::makeInstance($validatorObjectName, $validateAnnotation->options);
        }

        return $validator;
    }

    protected function initializeActionMethodArguments()
    {
        if (!$this->arguments) {
            $this->arguments = GeneralUtility::makeInstance(Arguments::class);
        }
        parent::initializeActionMethodArguments();
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
            $configuration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
            /** @var array $user */
            $user = $this->request->getArgument($argumentName);
            $this->getPropertyMappingConfiguration($configuration, $user);
        }
    }

    protected function getPropertyMappingConfiguration(
        ?PropertyMappingConfiguration $configuration,
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
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
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
     * @param FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function proxyAction(FrontendUser $user): ResponseInterface
    {
        $action = $this->request->hasArgument('form') ? 'form' : 'save';

        return (new ForwardResponse($action))
            ->withArguments(['user' => $user]);
    }

    /**
     * Remove an image and forward to the action where it was called
     *
     * @param FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    protected function removeImageAction(FrontendUser $user): ResponseInterface
    {
        /** @var FileReference $image */
        $image = $user->getImage()->current();

        $this->fileService->removeFile($image);
        $this->removeImageFromUserAndRequest($user);

        $this->request->setArgument('removeImage', false);

        $referringRequest = null;
        $referringRequestArguments = $this->request->getInternalArguments()['__referrer']['@request'] ?? null;
        if (is_string($referringRequestArguments)) {
            /** @var HashService $hashService */
            $hashService = GeneralUtility::makeInstance(HashService::class);
            $referrerArray = json_decode(
                $hashService->validateAndStripHmac($referringRequestArguments),
                true
            );
            $arguments = [];
            $referringRequest = new ReferringRequest();
            $referringRequest->setArguments(array_replace_recursive($arguments, $referrerArray));
        }

        if ($referringRequest !== null) {
            $response = (new ForwardResponse($referringRequest->getControllerActionName()))
                ->withControllerName($referringRequest->getControllerName())
                ->withExtensionName($referringRequest->getControllerExtensionName())
                ->withArguments($this->request->getArguments());
        } else {
            $response = new HtmlResponse($this->view->render());
        }

        return $response;
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
        /** @var PasswordHashFactory $passwordHashFactory */
        $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
        $passwordHash = $passwordHashFactory->getDefaultHashInstance('FE');
        return $passwordHash->getHashedPassword($password);
    }

    protected function persistAll()
    {
        GeneralUtility::getContainer()
            ->get(PersistenceManager::class)
            ->persistAll();
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
            ->setLinkAccessRestrictedPages(true)
            ->build();

        $this->redirectToUri($url);
    }

    protected function sendEmails(FrontendUser $user, string $action): FrontendUser
    {
        $action = ucfirst(str_replace('Action', '', $action));
        $type = $this->controller . $action;

        /** @var Mail $mailService */
        $mailService = GeneralUtility::getContainer()->get(Mail::class);

        if ($this->isNotifyAdmin($type)) {
            $user = $mailService->sendNotifyAdmin($user, $this->controller, $action);
        }

        if ($this->isNotifyUser($type)) {
            $user = $mailService->sendNotifyUser($user, $this->controller, $action);
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
     * @param FrontendUserGroup|string|int $userGroup
     *
     * @return bool
     */
    protected function isUserInUserGroup(FrontendUser $user, $userGroup): bool
    {
        $return = false;

        if ($userGroup instanceof FrontendUserGroup) {
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
     * Determines whether a user is in given user groups.
     *
     * @param FrontendUser $user
     * @param FrontendUserGroup[] $userGroups
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

    protected function getConfiguredUserGroups(int $currentUserGroup): array
    {
        $userGroups = $this->getUserGroupIds();
        $currentIndex = array_search($currentUserGroup, array_values($userGroups));

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
            $userGroup = (int)$this->settings[$settingsUserGroupKey];
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

        if ($userGroupIdToAdd) {
            /** @var FrontendUserGroup $userGroupToAdd */
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
            /** @var FileReference $image */
            $image = $user->getImage()->current();
            $this->fileService->moveFileFromTempFolderToUploadFolder($image);
        }
    }

    protected function autoLogin(FrontendUser $user, int $redirectPageId)
    {
        session_start();
        $this->autoLoginTriggered = true;

        $_SESSION['sf-register-user'] = GeneralUtility::hmac('auto-login::' . $user->getUid(), $GLOBALS['EXEC_TIME']);

        /** @var Registry $registry */
        $registry = GeneralUtility::makeInstance(Registry::class);
        $registry->set('sf-register', $_SESSION['sf-register-user'], $user->getUid());

        // if redirect was empty by now set it to current page
        if ($redirectPageId == 0) {
            $redirectPageId = $this->getTypoScriptFrontendController()->id;
        }

        // get configured redirect page id if given
        $userGroups = $user->getUsergroup();
        /** @var FrontendUserGroup $userGroup */
        foreach ($userGroups as $userGroup) {
            if ($userGroup->getFeloginRedirectPid()) {
                $redirectPageId = $userGroup->getFeloginRedirectPid();
                break;
            }
        }

        if ($redirectPageId > 0) {
            $this->redirectToPage((int)$redirectPageId);
        }
    }

    protected function userIsLoggedIn(): bool
    {
        /** @var \TYPO3\CMS\Core\Context\UserAspect $userAspect */
        $userAspect = $this->context->getAspect('frontend.user');
        return $userAspect->isLoggedIn();
    }

    /**
     * Determines the frontend user, either if it's already submitted, or by looking up the mail hash code.
     *
     * @param ?FrontendUser $user
     * @param ?string $hash
     *
     * @return ?FrontendUser
     */
    protected function determineFrontendUser(?FrontendUser $user, ?string $hash): ?FrontendUser
    {
        $frontendUser = null;

        $requestArguments = $this->request->getArguments();
        if (isset($requestArguments['user']) && $hash !== null) {
            $calculatedHash = GeneralUtility::hmac($requestArguments['action'] . '::' . $requestArguments['user']);
            if ($hash === $calculatedHash) {
                /** @var FrontendUser $frontendUser */
                $frontendUser = $this->userRepository->findByUidIgnoringDisabledField((int)$requestArguments['user']);
            }
        }

        return $frontendUser;
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

    protected function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
