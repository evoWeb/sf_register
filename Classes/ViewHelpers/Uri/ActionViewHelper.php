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

namespace Evoweb\SfRegister\ViewHelpers\Uri;

use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder as ExtbaseUriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\MissingArgumentException;

/**
 * Link Action view helper that automatically
 * adds a "hash" argument on the "user" and "action" arguments
 */
#[Autoconfigure(public: true)]
class ActionViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @var array<string, string|int|bool|string[]>
     * @api
     */
    protected array $arguments = [];

    public function __construct(protected HashService $hashService) {}

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('action', 'string', 'Target action');
        $this->registerArgument('arguments', 'array', 'Arguments for the controller action, associative array');
        $this->registerArgument('controller', 'string', 'Target controller. If NULL current controllerName is used');
        $this->registerArgument(
            'extensionName',
            'string',
            'Target Extension Name (without `tx_` prefix and no underscores). If NULL the current
             extension name is used'
        );
        $this->registerArgument('pluginName', 'string', 'Target plugin. If empty, the current plugin name is used');
        $this->registerArgument('pageUid', 'int', 'Target page. See TypoLink destination');
        $this->registerArgument('pageType', 'int', 'Type of the target page. See typolink.parameter');
        $this->registerArgument(
            'noCache',
            'bool',
            'Set this to disable caching for the target page. You should not need this.'
        );
        $this->registerArgument(
            'language',
            'string',
            'link to a specific language - defaults to the current language, use a language ID or "current"
            to enforce a specific language'
        );
        // section/format default to '' to match the core f:link.action/f:uri.action arguments: the
        // shared glue (createUriWithExtbaseContext) feeds them into the strictly string-typed
        // UriBuilder::setSection()/setFormat(), so a null default would raise a TypeError.
        $this->registerArgument('section', 'string', 'The anchor to be added to the URI', false, '');
        $this->registerArgument('format', 'string', 'The requested format, e.g. ".html', false, '');
        $this->registerArgument(
            'linkAccessRestrictedPages',
            'bool',
            'If set, links pointing to access restricted pages will still link to the page even though
             the page cannot be accessed.'
        );
        $this->registerArgument(
            'additionalParams',
            'array',
            'Additional query parameters that won\'t be prefixed like $arguments (overrule $arguments)'
        );
        $this->registerArgument('absolute', 'bool', 'If set, the URI of the rendered link is absolute');
        $this->registerArgument(
            'addQueryString',
            'bool',
            'If set, the current query parameters will be kept in the URI'
        );
        $this->registerArgument(
            'argumentsToBeExcludedFromQueryString',
            'array',
            'Arguments to be removed from the URI. Only active if $addQueryString = TRUE'
        );
    }

    public function render(): string
    {
        if (
            $this->arguments['action'] !== null
            && is_string($this->arguments['action'])
            && is_array($this->arguments['arguments'])
            && isset($this->arguments['arguments']['user'])
        ) {
            // An array "user" (used by the InviteToRegister email template for not-yet-persisted
            // invitees) contributes its "email" entry to the hash; a scalar user is used as-is.
            $user = $this->arguments['arguments']['user'];
            if (is_array($user)) {
                $email = $user['email'] ?? '';
                $user = is_scalar($email) ? (string)$email : '';
            } else {
                $user = is_scalar($user) ? (string)$user : '';
            }
            $this->arguments['arguments']['hash'] = $this->hashService->hmac(
                $this->arguments['action'] . '::' . $user,
                FrontendUserService::ADDITIONAL_SECRET
            );
        }

        $request = null;
        if ($this->renderingContext?->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }

        $childContent = $this->renderChildren();
        $childContent = is_string($childContent) ? $childContent : '';
        if ($request instanceof ExtbaseRequestInterface) {
            $uri = self::createUriWithExtbaseContext($request, $this->arguments);
            if ($uri === '') {
                return $childContent;
            }
            return $uri;
        }
        if ($request instanceof ServerRequestInterface && ApplicationType::fromRequest($request)->isFrontend()) {
            $linkResult = self::createFrontendLinkWithCoreContext($request, $this->arguments, $childContent);
            if ($linkResult === null) {
                return $childContent;
            }
            return $linkResult->getUrl();
        }
        throw new \RuntimeException(
            'The rendering context of ViewHelper f:uri.action is missing a valid request object.',
            1690360598
        );
    }

    /**
     * @param array<string, string|string[]|int|bool> $arguments
     */
    protected static function createFrontendLinkWithCoreContext(
        ServerRequestInterface $request,
        array $arguments,
        string $childContent
    ): ?LinkResultInterface {
        // No support for following arguments:
        //  * format
        $pageUid = isset($arguments['pageUid']) ? (int)$arguments['pageUid'] : null;
        $pageType = (int)$arguments['pageType'];
        $noCache = (bool)($arguments['noCache'] ?? false);
        $language = isset($arguments['language']) && is_string($arguments['language']) ? $arguments['language'] : null;
        $section = $arguments['section'];
        $linkAccessRestrictedPages = (bool)$arguments['linkAccessRestrictedPages'];
        $additionalParams = (array)$arguments['additionalParams'];
        $absolute = (bool)$arguments['absolute'];
        /** @var bool|string $addQueryString */
        $addQueryString = $arguments['addQueryString'];
        $argumentsToBeExcludedFromQueryString = (array)$arguments['argumentsToBeExcludedFromQueryString'];
        /** @var string|null $action */
        $action = $arguments['action'];
        /** @var string|null $controller */
        $controller = $arguments['controller'];
        /** @var string|null $extensionName */
        $extensionName = $arguments['extensionName'];
        /** @var string|null $pluginName */
        $pluginName = $arguments['pluginName'];
        $actionArguments = (array)$arguments['arguments'];

        $allExtbaseArgumentsAreSet = (
            is_string($extensionName) && $extensionName !== ''
            && is_string($pluginName) && $pluginName !== ''
            && is_string($controller) && $controller !== ''
            && is_string($action) && $action !== ''
        );
        if (!$allExtbaseArgumentsAreSet) {
            throw new MissingArgumentException(
                'ViewHelper f:link.action / f:uri.action needs either all extbase arguments set'
                . ' ("extensionName", "pluginName", "controller", "action")'
                . ' or needs a request implementing extbase RequestInterface.',
                1690370264
            );
        }

        // Provide extbase default and custom arguments as prefixed additional params
        $extbaseArgumentNamespace = 'tx_'
            . str_replace('_', '', strtolower($extensionName))
            . '_'
            . str_replace('_', '', strtolower($pluginName));
        $additionalParams[$extbaseArgumentNamespace] = array_replace(
            [
                'controller' => $controller,
                'action' => $action,
            ],
            $actionArguments
        );

        $typolinkConfiguration = [
            'parameter' => $pageUid ?: 'current',
        ];
        if ($pageType) {
            $typolinkConfiguration['parameter'] .= ',' . $pageType;
        }
        if ($language !== null) {
            $typolinkConfiguration['language'] = $language;
        }
        if ($noCache) {
            $typolinkConfiguration['no_cache'] = 1;
        }
        if ($section) {
            $typolinkConfiguration['section'] = $section;
        }
        if ($linkAccessRestrictedPages) {
            $typolinkConfiguration['linkAccessRestrictedPages'] = 1;
        }
        $typolinkConfiguration['queryParameters'] = $additionalParams;
        if ($absolute) {
            $typolinkConfiguration['forceAbsoluteUrl'] = true;
        }
        if ($addQueryString && $addQueryString !== 'false') {
            $typolinkConfiguration['addQueryString'] = $addQueryString;
            if ($argumentsToBeExcludedFromQueryString !== []) {
                $typolinkConfiguration['addQueryString.']['exclude'] = implode(',', $argumentsToBeExcludedFromQueryString);
            }
        }

        try {
            $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $cObj->setRequest($request);
            $linkFactory = GeneralUtility::makeInstance(LinkFactory::class);
            return $linkFactory->create($childContent, $typolinkConfiguration, $cObj);
        } catch (UnableToLinkException) {
            return null;
        }
    }

    /**
     * @param array<string, string|string[]|int|bool> $arguments
     */
    protected static function createUriWithExtbaseContext(ExtbaseRequestInterface $request, array $arguments): string
    {
        $format = is_string($arguments['format']) ? $arguments['format'] : 'html';
        $pageUid = (int)($arguments['pageUid'] ?? 0);
        $pageType = (int)$arguments['pageType'];
        $noCache = (bool)($arguments['noCache'] ?? false);
        $language = isset($arguments['language']) && is_string($arguments['language']) ? $arguments['language'] : null;
        $section = is_string($arguments['section']) ? $arguments['section'] : '';
        $linkAccessRestrictedPages = (bool)$arguments['linkAccessRestrictedPages'];
        $additionalParams = (array)$arguments['additionalParams'];
        $absolute = (bool)$arguments['absolute'];
        /** @var bool|string $addQueryString */
        $addQueryString = $arguments['addQueryString'];
        $argumentsToBeExcludedFromQueryString = (array)$arguments['argumentsToBeExcludedFromQueryString'];
        /** @var string|null $action */
        $action = $arguments['action'];
        /** @var string|null $controller */
        $controller = $arguments['controller'];
        /** @var string|null $extensionName */
        $extensionName = $arguments['extensionName'];
        /** @var string|null $pluginName */
        $pluginName = $arguments['pluginName'];
        $actionArguments = (array)$arguments['arguments'];

        $uriBuilder = GeneralUtility::makeInstance(ExtbaseUriBuilder::class);
        $uriBuilder
            ->reset()
            ->setRequest($request)
            ->setNoCache($noCache)
            ->setLanguage($language)
            ->setSection($section)
            ->setFormat($format)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri($absolute);

        if ($addQueryString && $addQueryString !== 'false') {
            $uriBuilder
                ->setAddQueryString($addQueryString)
                ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString);
        }

        if ($pageUid > 0) {
            $uriBuilder->setTargetPageUid($pageUid);
        }

        if ($pageType > 0) {
            $uriBuilder->setTargetPageType($pageType);
        }

        return $uriBuilder->uriFor($action, $actionArguments, $controller, $extensionName, $pluginName);
    }
}
