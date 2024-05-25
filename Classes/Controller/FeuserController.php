<?php

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

namespace Evoweb\SfRegister\Controller;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Evoweb\SfRegister\Controller\Event\InitializeActionEvent;
use Evoweb\SfRegister\Controller\Event\OverrideSettingsEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter;
use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\Mail;
use Evoweb\SfRegister\Validation\Validator\ConjunctionValidator;
use Evoweb\SfRegister\Validation\Validator\EmptyValidator;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator;
use Evoweb\SfRegister\Validation\Validator\SetPropertyNameInterface;
use Evoweb\SfRegister\Validation\Validator\SetRequestInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\SecurityAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Security\Exception\InvalidHashException;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorClassNameResolver;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * A frontend user controller containing common methods
 */
class FeuserController extends ActionController
{
    protected string $controller = '';

    protected array $ignoredActions = [];

    protected string $additionalSecret = 'sf-register-autologin';

    /**
     * The current view, as resolved by resolveView()
     *
     * @var TemplateView
     * @api
     */
    protected $view;

    /**
     * The current request.
     */
    protected RequestInterface $request;

    protected ?ResponseInterface $initializeResponse = null;

    public function __construct(
        protected Context $context,
        protected File $fileService,
        protected FrontendUserRepository $userRepository,
        protected FrontendUserGroupRepository $userGroupRepository
    ) {}

    protected function getErrorFlashMessage(): bool
    {
        // Disable flash messages
        return false;
    }

    protected function initializeActionMethodValidators(): void
    {
        $this->settings['hasOriginalRequest'] = $this->request->getAttribute('extbase')->getOriginalRequest() !== null;

        if (!is_array($this->settings['fields']['selected'] ?? [])) {
            $this->settings['fields']['selected'] = explode(',', $this->settings['fields']['selected']);
        }
        if (!is_array($this->settings['fields']['selected'])) {
            $this->settings['fields']['selected'] = [];
        }

        if ($this->actionIsIgnored() || $this->skipValidation()) {
            parent::initializeActionMethodValidators();
        } else {
            foreach ($this->arguments as $argumentName => $argument) {
                if (!in_array($argumentName, ['user', 'password', 'email'])) {
                    continue;
                }
                $this->modifyValidatorsBasedOnSettings(
                    $argument,
                    $this->settings['validation'][strtolower($this->controller)] ?? []
                );
            }
        }
    }

    protected function actionIsIgnored(): bool
    {
        if (is_array($this->settings['ignoredActions'][$this->controller] ?? '')) {
            $this->ignoredActions = array_merge(
                $this->settings['ignoredActions'][$this->controller],
                $this->ignoredActions
            );
        }
        return in_array($this->actionMethodName, $this->ignoredActions);
    }

    protected function skipValidation(): bool
    {
        return false;
    }

    protected function modifyValidatorsBasedOnSettings(Argument $argument, array $configuredValidators): void
    {
        $parser = new DocParser();

        /** @var UserValidator $validator */
        $validator = GeneralUtility::makeInstance(UserValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (!in_array($fieldName, $this->settings['fields']['selected'] ?? [])) {
                continue;
            }

            if (is_array($configuredValidator) && count($configuredValidator) === 1) {
                $configuredValidator = reset($configuredValidator);
            }

            if (!is_array($configuredValidator)) {
                try {
                    $validatorInstance = $this->getValidatorByConfiguration(
                        $configuredValidator,
                        $parser,
                        $fieldName
                    );
                } catch (\Exception) {
                    continue;
                }
            } else {
                /** @var ConjunctionValidator $validatorInstance */
                $validatorInstance = $this->validatorResolver->createValidator(
                    ConjunctionValidator::class
                );
                foreach ($configuredValidator as $individualConfiguredValidator) {
                    try {
                        $individualValidatorInstance = $this->getValidatorByConfiguration(
                            $individualConfiguredValidator,
                            $parser,
                            $fieldName
                        );
                    } catch (\Exception) {
                        continue;
                    }

                    $validatorInstance->addValidator($individualValidatorInstance);
                }
            }

            $validator->addPropertyValidator($fieldName, $validatorInstance);
        }

        $this->addUidValidator($validator);

        $argument->setValidator($validator);
    }

    /**
     * @throws \ReflectionException
     * @throws NoSuchValidatorException
     * @throws AnnotationException
     */
    protected function getValidatorByConfiguration(
        string $configuration,
        DocParser $parser,
        string $fieldName
    ): ValidatorInterface {
        if (!str_contains($configuration, '"') && !str_contains($configuration, '(')) {
            $configuration = '"' . $configuration . '"';
        }

        /** @var Extbase\Validate $validateAnnotation */
        $validateAnnotation = current($parser->parse('@' . Extbase\Validate::class . '(' . $configuration . ')'));
        $validatorObjectName = ValidatorClassNameResolver::resolve($validateAnnotation->validator);

        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance($validatorObjectName);
        // @extensionScannerIgnoreLine
        $validator->setOptions($validateAnnotation->options);

        if ($validator instanceof SetRequestInterface) {
            $validator->setRequest($this->request);
        }

        if ($validator instanceof SetPropertyNameInterface) {
            $validator->setPropertyName($fieldName);
        }

        return $validator;
    }

    protected function addUidValidator(UserValidator $validator): UserValidator
    {
        if (in_array($this->controller, ['Edit', 'Delete'])) {
            $validatorName = EqualCurrentUserValidator::class;
        } else {
            $validatorName = EmptyValidator::class;
        }

        try {
            $validatorInstance = GeneralUtility::makeInstance($validatorName);
            $validator->addPropertyValidator('uid', $validatorInstance);
        } catch (\Exception) {
        }

        return $validator;
    }

    protected function initializeActionMethodArguments(): void
    {
        if (!isset($this->arguments)) {
            $this->arguments = GeneralUtility::makeInstance(Arguments::class);
        }
        parent::initializeActionMethodArguments();

        $event = new OverrideSettingsEvent(
            $this->settings,
            $this->controller,
            $this->request->getAttribute('currentContentObject')
        );
        $this->eventDispatcher->dispatch($event);
        $this->settings = $event->getSettings();
    }

    protected function initializeAction(): void
    {
        $this->setTypeConverter();

        if ($this->settings['processInitializeActionEvent'] ?? false) {
            $event = new InitializeActionEvent($this, $this->settings, null);
            $this->eventDispatcher->dispatch($event);
            $this->initializeResponse = $event->getResponse();
        }

        if (
            $this->request->getControllerActionName() != 'removeImage'
            && $this->request->hasArgument('removeImage')
            && $this->request->getArgument('removeImage')
        ) {
            $this->initializeResponse = new ForwardResponse('removeImage');
        }
    }

    protected function setTypeConverter(): void
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
            (string)PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
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
     * Initialize a view object to be able to set templateRootPath from flex form
     */
    protected function initializeView(): void
    {
        if (($this->settings['templateRootPath'] ?? '') !== '') {
            $templateRootPath = GeneralUtility::getFileAbsFileName($this->settings['templateRootPath']);
            if (GeneralUtility::isAllowedAbsPath($templateRootPath)) {
                $templateRootPaths = $this->view->getTemplateRootPaths();
                $this->view->setTemplateRootPaths(array_merge($templateRootPaths, [$templateRootPath]));
            }
        }
    }

    protected function callActionMethod(RequestInterface $request): ResponseInterface
    {
        if ($this->initializeResponse) {
            return $this->initializeResponse;
        }
        return parent::callActionMethod($request);
    }

    #[Extbase\IgnoreValidation(['value' => 'user'])]
    public function proxyAction(FrontendUser $user): ResponseInterface
    {
        $action = $this->request->hasArgument('form') ? 'form' : 'save';

        return (new ForwardResponse($action))
            ->withArguments(['user' => $user]);
    }

    /**
     * Remove an image and forward to the action where it was called
     *
     * @throws IllegalObjectTypeException
     * @throws InvalidArgumentForHashGenerationException
     * @throws InvalidArgumentNameException
     * @throws InvalidHashException
     * @throws UnknownObjectException
     */
    #[Extbase\IgnoreValidation(['value' => 'user'])]
    protected function removeImageAction(FrontendUser $user): ResponseInterface
    {
        /** @var FileReference $image */
        $image = $user->getImage()->current();

        $this->fileService->removeFile($image);
        $this->removeImageFromUserAndRequest($user);

        $this->request = $this->request->withArgument('removeImage', false);

        /** @var ForwardResponse $response */
        $response = $this->forwardToReferringRequest();
        return $response->withArguments(['user' => $user]);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
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
        $this->request = $this->request->withArgument('user', $requestUser);

        return $user;
    }

    public function encryptPassword(string $password): string
    {
        try {
            /** @var PasswordHashFactory $passwordHashFactory */
            $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
            $passwordHash = $passwordHashFactory->getDefaultHashInstance('FE');
            return $passwordHash->getHashedPassword($password);
        } catch (\Exception) {
            return (string)time();
        }
    }

    protected function persistAll(): void
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();
    }

    protected function redirectToPage(int $pageId): ResponseInterface
    {
        $this->uriBuilder->reset();

        $uri = $this->uriBuilder
            ->setTargetPageUid($pageId)
            ->setLinkAccessRestrictedPages(true)
            ->build();
        $uri = $this->addBaseUriIfNecessary($uri);

        return $this->redirectToUri($uri);
    }

    protected function autoLogin(FrontendUserInterface $user, int $redirectPageId): void
    {
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
            $redirectPageId = $this->getTypoScriptFrontendController()->id;
        }

        session_start();

        /** @var HashService $hashService */
        $hashService = GeneralUtility::makeInstance(HashService::class);
        $_SESSION['sf-register-user'] = $hashService->hmac(
            'auto-login::' . $user->getUid(),
            $this->additionalSecret
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

            $response = $this->redirectToPage($redirectPageId);
            $response = $response
                ->withHeader(
                    'location',
                    $response->getHeaderLine('location') . '?' . http_build_query($parameter)
                );
            throw new PropagateResponseException($response);
        }
    }

    protected function sendEmails(FrontendUser $user, string $action): FrontendUserInterface
    {
        $action = ucfirst(str_replace('Action', '', $action));
        $type = $this->controller . $action;

        /** @var Mail $mailService */
        $mailService = GeneralUtility::makeInstance(Mail::class);
        $mailService->setRequest($this->request);

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
        $notifySettings = $this->settings['notifyAdmin'] ?? [];
        return !empty($notifySettings[$type]);
    }

    protected function isNotifyUser(string $type): bool
    {
        $type = lcfirst($type);
        $notifySettings = $this->settings['notifyUser'] ?? [];
        return !empty($notifySettings[$type]);
    }

    /**
     * Determines whether a user is in a given user group.
     */
    protected function isUserInUserGroup(FrontendUser $user, int $userGroupUid): bool
    {
        $in = false;
        /** @var FrontendUserGroup $userGroup */
        foreach ($user->getUsergroup() as $userGroup) {
            $in = $in || $userGroup->getUid() == $userGroupUid;
        }
        return $in;
    }

    /**
     * Determines whether a user is in given user groups.
     */
    protected function isUserInUserGroups(FrontendUser $user, array $userGroupUids): bool
    {
        $return = false;

        foreach ($userGroupUids as $userGroupUid) {
            if ($this->isUserInUserGroup($user, $userGroupUid)) {
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
            $userGroup = (int)($this->settings[$settingsUserGroupKey] ?? 0);
            if ($userGroup) {
                $userGroups[$settingsUserGroupKey] = $userGroup;
            }
        }

        return $userGroups;
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

    protected function removePreviousUserGroups(FrontendUser $user): void
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

    protected function moveTemporaryImage(FrontendUser $user): void
    {
        if ($user->getImage()->count()) {
            /** @var FileReference $image */
            $image = $user->getImage()->current();
            $this->fileService->moveFileFromTempFolderToUploadFolder($image);
        }
    }

    protected function userIsLoggedIn(): bool
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

    /**
     * Determines the frontend user, either if it's already submitted, or by looking up the mail hash code.
     */
    protected function determineFrontendUser(?FrontendUser $user, ?string $hash): ?FrontendUser
    {
        $frontendUser = $user;

        $requestArguments = $this->request->getArguments();
        if (isset($requestArguments['user']) && $hash !== null) {
            /** @var HashService $hashService */
            $hashService = GeneralUtility::makeInstance(HashService::class);
            $calculatedHash = $hashService->hmac(
                $requestArguments['action'] . '::' . $requestArguments['user'],
                $this->additionalSecret
            );
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
        $confirmEmailPostCreate = (bool)($this->settings['confirmEmailPostCreate'] ?? false);
        // User     [plugin.tx_sfregister.settings.confirmEmailPostAccept]
        $acceptEmailPostCreate = (bool)($this->settings['acceptEmailPostCreate'] ?? false);
        // Admin    [plugin.tx_sfregister.settings.acceptEmailPostConfirm]
        $confirmEmailPostAccept = (bool)($this->settings['confirmEmailPostAccept'] ?? false);
        // User     [plugin.tx_sfregister.settings.confirmEmailPostCreate]
        $acceptEmailPostConfirm = (bool)($this->settings['acceptEmailPostConfirm'] ?? false);

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
        return $this->request->getAttribute('frontend.controller');
    }
}
