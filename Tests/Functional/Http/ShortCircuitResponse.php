<?php

namespace Evoweb\SfRegister\Tests\Functional\Http;

use TYPO3\CMS\Core\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class ShortCircuitResponse extends Response
{
    public function __construct(protected ?ServerRequestInterface $request)
    {
        parent::__construct();
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
