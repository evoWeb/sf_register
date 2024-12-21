<?php

namespace EvowebTests\TestClasses\Error\PageErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Error\PageErrorHandler\PageErrorHandlerInterface;
use TYPO3\CMS\Core\Http\Response;

class PhpErrorHandler implements PageErrorHandlerInterface
{
    /**
     * @param array<string, mixed> $reasons
     */
    public function handlePageError(
        ServerRequestInterface $request,
        string $message,
        array $reasons = []
    ): ResponseInterface {
        return new Response();
    }
}
