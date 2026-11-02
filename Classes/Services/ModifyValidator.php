<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Services;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Evoweb\SfRegister\Annotation\Validate;
use Evoweb\SfRegister\Controller\FeuserController;
use Evoweb\SfRegister\Validation\Validator\ConjunctionValidator;
use Evoweb\SfRegister\Validation\Validator\EmptyValidator;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator;
use Evoweb\SfRegister\Validation\Validator\SetPropertyNameInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

#[Autoconfigure(public: true)]
class ModifyValidator
{
    protected LoggerInterface $logger;

    public function __construct(
        protected ValidatorResolver $validatorResolver,
        LogManager $logManager,
    ) {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param array<string, array<string>|string> $settings
     * @param string[] $ignoredActions
     */
    public function shouldValidationBeModified(
        FeuserController $controller,
        array $settings,
        RequestInterface $request,
        string $actionMethodName,
        array $ignoredActions,
    ): bool {
        $controllerName = $controller->getControllerName();
        return !(
            $this->actionIsIgnored($controllerName, $settings, $actionMethodName, $ignoredActions)
            || $this->skipValidation($controllerName, $request, $actionMethodName)
        );
    }

    /**
     * @param array<string, array<string>|string> $settings
     * @param string[] $ignoredActions
     */
    protected function actionIsIgnored(
        string $controllerName,
        array $settings,
        string $actionMethodName,
        array $ignoredActions,
    ): bool {
        $controllerIgnoredActions = $settings['ignoredActions'][$controllerName] ?? [];
        $controllerIgnoredActions = is_array($controllerIgnoredActions) ? $controllerIgnoredActions : [];
        $ignoredActions = array_merge($controllerIgnoredActions, $ignoredActions);
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

    /**
     * @param array<string, array<string>|string> $settings
     */
    public function modifyArgumentValidators(
        FeuserController $controller,
        array $settings,
        RequestInterface $request,
        Arguments $arguments,
    ): Arguments {
        /**
         * @var string $argumentName
         * @var Argument $argument
         */
        foreach ($arguments as $argumentName => $argument) {
            if (!in_array($argumentName, ['user', 'password', 'email'])) {
                continue;
            }

            $this->modifyValidatorsBasedOnSettings(
                $argument,
                $request,
                $settings,
                $controller,
            );
        }
        return $arguments;
    }

    /**
     * @param array<string, string|string[]> $settings
     */
    protected function modifyValidatorsBasedOnSettings(
        Argument $argument,
        RequestInterface $request,
        array $settings,
        FeuserController $controller,
    ): void {
        /** @var array<string, string[]> $configuredValidators */
        $configuredValidators = $settings['validation'][strtolower($controller->getControllerName())] ?? [];
        $parser = new DocParser();

        /** @var UserValidator $validator */
        $validator = $this->validatorResolver->createValidator(UserValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (
                !isset($settings['fields'])
                || !is_array($settings['fields'])
                || !isset($settings['fields']['selected'])
                || !is_array($settings['fields']['selected'])
                || !in_array($fieldName, $settings['fields']['selected'])
            ) {
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
                        $fieldName,
                        $request,
                    );
                } catch (\Exception $exception) {
                    $this->logger->debug($exception->getMessage());
                    continue;
                }
            } else {
                /** @var ConjunctionValidator $validatorInstance */
                $validatorInstance = $this->validatorResolver->createValidator(ConjunctionValidator::class);
                foreach ($configuredValidator as $individualConfiguredValidator) {
                    try {
                        if (!is_string($individualConfiguredValidator)) {
                            continue;
                        }
                        $individualValidatorInstance = $this->getValidatorByConfiguration(
                            $individualConfiguredValidator,
                            $parser,
                            $fieldName,
                            $request,
                        );
                    } catch (\Exception $exception) {
                        $this->logger->debug($exception->getMessage());
                        continue;
                    }

                    if ($individualValidatorInstance) {
                        $validatorInstance->addValidator($individualValidatorInstance);
                    }
                }
            }

            if ($validatorInstance) {
                $validator->addPropertyValidator($fieldName, $validatorInstance);
            }
        }

        $this->addUidValidator($controller->getControllerName(), $validator);

        $argument->setValidator($validator);
    }

    /**
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    protected function getValidatorByConfiguration(
        string $configuration,
        DocParser $parser,
        string $fieldName,
        RequestInterface $request,
    ): ?ValidatorInterface {
        if (!str_contains($configuration, '"') && !str_contains($configuration, '(')) {
            $configuration = '"' . $configuration . '"';
        }

        $validator = null;
        $configuration = '@' . Validate::class . '(' . $configuration . ')';
        $validateAnnotation = current($parser->parse($configuration));
        // The DocParser instantiates the Evoweb\SfRegister\Annotation\Validate annotation named in
        // $configuration - the instanceof must check exactly that class (parse() may return false);
        // a validator has to be resolved for every valid annotation.
        if ($validateAnnotation instanceof Validate) {
            $validator = $this->validatorResolver->createValidator(
                $validateAnnotation->validator,
                $validateAnnotation->options,
                $request
            );
        }

        if ($validator instanceof SetPropertyNameInterface) {
            $validator->setPropertyName($fieldName);
        }

        return $validator;
    }

    protected function addUidValidator(string $controllerName, UserValidator $validator): UserValidator
    {
        if (in_array($controllerName, ['Edit', 'Delete'])) {
            $validatorName = EqualCurrentUserValidator::class;
        } else {
            $validatorName = EmptyValidator::class;
        }

        try {
            $validatorInstance = $this->validatorResolver->createValidator($validatorName);
            if ($validatorInstance instanceof ValidatorInterface) {
                $validator->addPropertyValidator('uid', $validatorInstance);
            }
        } catch (\Exception) {
        }

        return $validator;
    }
}
