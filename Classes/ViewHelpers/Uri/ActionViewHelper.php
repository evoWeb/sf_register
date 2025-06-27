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

use Evoweb\SfRegister\ViewHelpers\ActionUriTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Link Action view helper that automatically
 * adds a "hash" argument on the "user" and "action" arguments
 */
class ActionViewHelper extends AbstractViewHelper
{
    use ActionUriTrait;

    protected $escapeOutput = false;

    public function __construct(
        protected HashService $hashService,
        protected ContentObjectRenderer $contentObjectRenderer,
        protected LinkFactory $linkFactory,
        protected UriBuilder $uriBuilder,
    )
    {
    }

    protected function renderFrontendLinkWithCoreContext(ServerRequestInterface $request): string
    {
        try {
            return $this->buildLinkResult($request)->getUrl();
        } catch (UnableToLinkException) {
            return '';
        }
    }

    protected function renderWithExtbaseContext(RequestInterface $request): string
    {
        return $this->buildUri($request);
    }
}
