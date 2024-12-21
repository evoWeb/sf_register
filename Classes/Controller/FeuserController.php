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

namespace Evoweb\SfRegister\Controller;

use Evoweb\SfRegister\Controller\Event\InitializeActionEvent;
use Evoweb\SfRegister\Controller\Event\OverrideSettingsEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter;
use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;

/**
 * A frontend user controller containing common methods
 */
class FeuserController extends ActionController
{
    protected string $controller = '';

    /**
     * @var string[]
     */
    protected array $ignoredActions = [];

    /**
     * @var FluidViewAdapter
     */
    protected $view;

    /**
     * The current request.
     */
    protected RequestInterface $request;

    protected ?ResponseInterface $initializeResponse = null;

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
    ) {}

    protected function getErrorFlashMessage(): bool
    {
        // Disable flash messages
        return false;
    }

    protected function initializeActionMethodValidators(): void
    {
        $this->modifySettingsBeforeActionMethodValidators();

        if ($this->modifyValidator->shouldValidationBeModified(
            $this,
            $this->settings,
            $this->request,
            $this->actionMethodName,
            $this->ignoredActions,
        )) {
            $this->arguments = $this->modifyValidator->modifyArgumentValidators(
                $this,
                $this->settings,
                $this->request,
                $this->arguments,
            );
        } else {
            parent::initializeActionMethodValidators();
        }
    }

    protected function modifySettingsBeforeActionMethodValidators(): void
    {
        $this->settings['hasOriginalRequest'] = $this->request->getAttribute('extbase')->getOriginalRequest() !== null;

        if (!is_array($this->settings['fields']['selected'] ?? [])) {
            $this->settings['fields']['selected'] = explode(',', $this->settings['fields']['selected']);
        }
        if (!is_array($this->settings['fields']['selected'])) {
            $this->settings['fields']['selected'] = [];
        }
    }

    protected function initializeActionMethodArguments(): void
    {
        if (!isset($this->arguments)) {
            $this->arguments = GeneralUtility::makeInstance(Arguments::class);
        }

        // Convert image if type is UploadedFile to array
        if ($this->request->hasArgument('user')) {
            $user = $this->request->getArgument('user');
            if (is_array($user) && is_array($user['image'] ?? false) && !empty($user['image'])) {
                array_walk($user['image'], function (&$image) {
                    if ($image instanceof UploadedFile) {
                        $image = [
                            'name' => $image->getClientFilename(),
                            'tmp_name' => $image->getTemporaryFileName(),
                            // @extensionScannerIgnoreLine
                            'size' => $image->getSize(),
                            'error' => $image->getError(),
                            'type' => $image->getClientMediaType(),
                        ];
                    }
                });
                $this->request = $this->request->withArgument('user', $user);
            }
        }

        parent::initializeActionMethodArguments();

        $event = new OverrideSettingsEvent(
            $this->settings,
            $this->getControllerName(),
            $this->request->getAttribute('currentContentObject'),
        );
        $this->settings = $this->eventDispatcher->dispatch($event)->getSettings();
    }

    public function getControllerName(): string
    {
        preg_match('/Feuser(?<controller>.*)Controller/', static::class, $matches);
        return $matches['controller'] ?? '';
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
            /** @var array<string, mixed> $user */
            $user = $this->request->getArgument($argumentName);
            $this->getPropertyMappingConfiguration($configuration, $user);
        }
    }

    /**
     * @param FrontendUser|array<string, mixed> $userData
     */
    protected function getPropertyMappingConfiguration(
        ?PropertyMappingConfiguration $configuration,
        FrontendUser|array $userData = [],
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
            true,
        );

        $configuration->forProperty('image.0')
            ->setTypeConverterOptions(
                UploadedFileReferenceConverter::class,
                [
                    UploadedFileReferenceConverter::CONFIGURATION_FILE_VALIDATORS =>
                        $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                    UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER =>
                        $this->fileService->getTempFolder()->getCombinedIdentifier(),
                ]
            );

        $configuration->forProperty('dateOfBirth')
            ->setTypeConverterOptions(
                DateTimeConverter::class,
                [
                    DateTimeConverter::CONFIGURATION_USER_DATA => $userData,
                ],
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
                $templatePaths = $this->view->getRenderingContext()->getTemplatePaths();
                $templatePaths->setTemplateRootPaths(
                    array_merge($templatePaths->getTemplateRootPaths(), [$templateRootPath])
                );
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
     * @throws InvalidArgumentNameException
     * @throws UnknownObjectException
     */
    #[Extbase\IgnoreValidation(['value' => 'user'])]
    protected function removeImageAction(FrontendUser $user): ResponseInterface
    {
        $images = $user->getImage();

        array_walk(
            $images,
            function ($image) use ($user) {
                $this->fileService->removeFile($image);
                $this->removeImageFromUserAndRequest($user);
            }
        );

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
            $localUser = $this->userRepository->findByIdentifier($user->getUid());
            $localUser->removeImage();
            $this->userRepository->update($localUser);

            $this->persistAll();
        }

        $user->removeImage();

        /** @var array<string, mixed> $requestUser */
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
}
