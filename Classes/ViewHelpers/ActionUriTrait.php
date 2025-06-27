<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\ViewHelpers;

use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\ViewHelpers\Link\ActionViewHelper as LinkActionViewHelper;
use Evoweb\SfRegister\ViewHelpers\Uri\ActionViewHelper as UriActionViewHelper;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

/**
 * @internal
 */
trait ActionUriTrait
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('action', 'string', 'Target action');
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
        $this->registerArgument('section', 'string', 'The anchor to be added to the URI');
        $this->registerArgument('format', 'string', 'The requested format, e.g. ".html');
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
        $this->registerArgument('arguments', 'array', 'Arguments for the controller action, associative array');
    }

    public function render(): string
    {
        if (
            $this->arguments['action'] !== null
            && is_array($this->arguments['arguments'])
            && isset($this->arguments['arguments']['user'])
        ) {
            $this->arguments['arguments']['hash'] = $this->hashService->hmac(
                $this->arguments['action'] . '::' . $this->arguments['arguments']['user'],
                FrontendUserService::ADDITIONAL_SECRET
            );
        }

        $request = null;
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        if ($request instanceof ExtbaseRequestInterface) {
            return $this->renderWithExtbaseContext($request);
        }
        if ($request instanceof ServerRequestInterface && ApplicationType::fromRequest($request)->isFrontend()) {
            return $this->renderFrontendLinkWithCoreContext($request);
        }
        throw new RuntimeException(
            'The rendering context of ViewHelper sf:link.action is missing a valid request object.',
            1690365240
        );
    }

    /**
     * Build and return LinkResultInterrace
     *
     * @throws UnableToLinkException
     * @see LinkActionViewHelper::renderFrontendLinkWithCoreContext()
     * @see UriActionViewHelper::renderFrontendLinkWithCoreContext()
     */
    protected function buildLinkResult(ServerRequestInterface $request): LinkResultInterface
    {
        // No support for following arguments:
        //  * format
        $pageUid = (int)($this->arguments['pageUid'] ?? 0);
        $pageType = (int)($this->arguments['pageType'] ?? 0);
        $noCache = (bool)($this->arguments['noCache'] ?? false);
        /** @var string|null $language */
        $language = isset($this->arguments['language']) ? (string)$this->arguments['language'] : null;
        /** @var string|null $section */
        $section = $this->arguments['section'] ?? null;
        $linkAccessRestrictedPages = (bool)($this->arguments['linkAccessRestrictedPages'] ?? false);
        /** @var array<string, mixed>|null $additionalParams */
        $additionalParams = $this->arguments['additionalParams'] ?? null;
        $absolute = (bool)($this->arguments['absolute'] ?? false);
        /** @var bool|string $addQueryString */
        $addQueryString = $this->arguments['addQueryString'] ?? false;
        /** @var array<string>|null $argumentsToBeExcludedFromQueryString */
        $argumentsToBeExcludedFromQueryString = $this->arguments['argumentsToBeExcludedFromQueryString'] ?? null;
        /** @var string|null $action */
        $action = $this->arguments['action'] ?? null;
        /** @var string|null $controller */
        $controller = $this->arguments['controller'] ?? null;
        /** @var string|null $extensionName */
        $extensionName = $this->arguments['extensionName'] ?? null;
        /** @var string|null $pluginName */
        $pluginName = $this->arguments['pluginName'] ?? null;
        /** @var array<string, mixed> $arguments */
        $arguments = $this->arguments['arguments'] ?? [];

        $allExtbaseArgumentsAreSet = (
            is_string($extensionName) && $extensionName !== ''
            && is_string($pluginName) && $pluginName !== ''
            && is_string($controller) && $controller !== ''
            && is_string($action) && $action !== ''
        );
        if (!$allExtbaseArgumentsAreSet) {
            throw new RuntimeException(
                'ViewHelper sf:link.action needs either all extbase arguments set'
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
        $additionalParams ??= [];
        $additionalParams[$extbaseArgumentNamespace] = array_replace(
            [
                'controller' => $controller,
                'action' => $action,
            ],
            $arguments
        );

        $typolinkConfiguration = [
            'parameter' => $pageUid,
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
        $typolinkConfiguration['additionalParams'] = HttpUtility::buildQueryString($additionalParams, '&');
        if ($absolute) {
            $typolinkConfiguration['forceAbsoluteUrl'] = true;
        }
        if ($addQueryString && $addQueryString !== 'false') {
            $typolinkConfiguration['addQueryString'] = $addQueryString;
            if ($argumentsToBeExcludedFromQueryString !== []) {
                $typolinkConfiguration['addQueryString.']['exclude'] =
                    implode(',', $argumentsToBeExcludedFromQueryString);
            }
        }

        $this->contentObjectRenderer->setRequest($request);
        return $this->linkFactory->create(
            (string)$this->renderChildren(),
            $typolinkConfiguration,
            $this->contentObjectRenderer
        );
    }

    /**
     * Build and return URI as string
     *
     * @see LinkActionViewHelper::renderWithExtbaseContext()
     * @see UriActionViewHelper::renderWithExtbaseContext()
     */
    protected function buildUri(RequestInterface $request): string
    {
        $action = $this->arguments['action'];
        $controller = $this->arguments['controller'];
        $extensionName = $this->arguments['extensionName'];
        $pluginName = $this->arguments['pluginName'];
        $pageUid = (int)$this->arguments['pageUid'] ?: null;
        $pageType = (int)($this->arguments['pageType'] ?? 0);
        $noCache = (bool)($this->arguments['noCache'] ?? false);
        $language = isset($this->arguments['language']) ? (string)$this->arguments['language'] : null;
        $section = (string)$this->arguments['section'];
        $format = (string)$this->arguments['format'];
        $linkAccessRestrictedPages = (bool)($this->arguments['linkAccessRestrictedPages'] ?? false);
        $additionalParams = (array)$this->arguments['additionalParams'];
        $absolute = (bool)($this->arguments['absolute'] ?? false);
        $addQueryString = $this->arguments['addQueryString'] ?? false;
        $argumentsToBeExcludedFromQueryString = (array)$this->arguments['argumentsToBeExcludedFromQueryString'];
        $parameters = $this->arguments['arguments'];

        $this->uriBuilder
            ->reset()
            ->setRequest($request)
            ->setTargetPageType($pageType)
            ->setNoCache($noCache)
            ->setLanguage($language)
            ->setSection($section)
            ->setFormat($format)
            ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri($absolute)
            ->setAddQueryString($addQueryString)
            ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString);

        if (MathUtility::canBeInterpretedAsInteger($pageUid)) {
            $this->uriBuilder->setTargetPageUid((int)$pageUid);
        }
        return $this->uriBuilder->uriFor($action, $parameters, $controller, $extensionName, $pluginName);
    }
}
