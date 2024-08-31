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

namespace Evoweb\SfRegister\Services;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Evoweb\SfRegister\Controller\FeuserController;
use Evoweb\SfRegister\Validation\Validator\ConjunctionValidator;
use Evoweb\SfRegister\Validation\Validator\EmptyValidator;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator;
use Evoweb\SfRegister\Validation\Validator\SetPropertyNameInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorClassNameResolver;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class ModifyValidator
{
    public function __construct(protected ValidatorResolver $validatorResolver)
    {
    }

    public function shouldValidationBeModified(
        FeuserController $controller,
        array $settings,
        RequestInterface $request,
        string $actionMethodName,
        array $ignoredActions,
    ): bool {
        $controllerName = $controller->getControllerName();
        return !$this->actionIsIgnored($controllerName, $settings, $actionMethodName, $ignoredActions)
            && $this->skipValidation($controllerName, $request, $actionMethodName);
    }

    protected function actionIsIgnored(
        string $controllerName,
        array $settings,
        string $actionMethodName,
        array $ignoredActions,
    ): bool {
        $ignoredActions = array_merge($settings['ignoredActions'][$controllerName] ?? [], $ignoredActions);
        return in_array($actionMethodName, $ignoredActions);
    }

    protected function skipValidation(string $controllerName, RequestInterface $request, string $actionMethodName): bool
    {
        if ($controllerName !== 'Create') {
            return false;
        }

        $user = $request->hasArgument('user') ? $request->getArgument('user') : null;
        return $actionMethodName === 'formAction' && is_array($user) && ($user['byInvitation'] ?? '0');
    }

    public function modifyArgumentValidators(
        FeuserController $controller,
        array $settings,
        RequestInterface $request,
        Arguments $arguments,
    ): Arguments {
        $controllerName = $controller->getControllerName();
        foreach ($arguments as $argumentName => $argument) {
            if (!in_array($argumentName, ['user', 'password', 'email'])) {
                continue;
            }
            $this->modifyValidatorsBasedOnSettings(
                $controllerName,
                $settings,
                $request,
                $argument,
                $this->settings['validation'][strtolower($controllerName)] ?? []
            );
        }
        return $arguments;
    }

    protected function modifyValidatorsBasedOnSettings(
        string $controllerName,
        array $settings,
        RequestInterface $request,
        Argument $argument,
        array $configuredValidators,
    ): void {
        $parser = new DocParser();

        /** @var UserValidator $validator */
        $validator = GeneralUtility::makeInstance(UserValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (!in_array($fieldName, $settings['fields']['selected'] ?? [])) {
                continue;
            }

            if (is_array($configuredValidator) && count($configuredValidator) === 1) {
                $configuredValidator = reset($configuredValidator);
            }

            if (!is_array($configuredValidator)) {
                try {
                    $validatorInstance = $this->getValidatorByConfiguration(
                        $request,
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
                            $request,
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

        $this->addUidValidator($controllerName, $validator);

        $argument->setValidator($validator);
    }

    /**
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    protected function getValidatorByConfiguration(
        RequestInterface $request,
        string $configuration,
        DocParser $parser,
        string $fieldName,
    ): ?ValidatorInterface {
        if (!str_contains($configuration, '"') && !str_contains($configuration, '(')) {
            $configuration = '"' . $configuration . '"';
        }

        /** @var Extbase\Validate $validateAnnotation */
        $validateAnnotation = current($parser->parse('@' . Extbase\Validate::class . '(' . $configuration . ')'));
        try {
            $validatorObjectName = ValidatorClassNameResolver::resolve($validateAnnotation->validator);
            /** @var ValidatorInterface $validator */
            $validator = GeneralUtility::makeInstance($validatorObjectName);
            // @extensionScannerIgnoreLine
            $validator->setOptions($validateAnnotation->options);

            if ($validator instanceof AbstractValidator) {
                $validator->setRequest($request);
            }

            if ($validator instanceof SetPropertyNameInterface) {
                $validator->setPropertyName($fieldName);
            }

            return $validator;
        } catch (NoSuchValidatorException $e) {
            /** @var LogManager $logManager */
            $logManager = GeneralUtility::makeInstance(LogManager::class);
            $logManager->getLogger(__CLASS__)->debug($e->getMessage());
            return null;
        }
    }

    protected function addUidValidator(string $controllerName, UserValidator $validator): UserValidator
    {
        if (in_array($controllerName, ['Edit', 'Delete'])) {
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
}
