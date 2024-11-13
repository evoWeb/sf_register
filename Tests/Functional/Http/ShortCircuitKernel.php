<?php

namespace Evoweb\SfRegister\Tests\Functional\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShortCircuitKernel implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new ShortCircuitResponse($request);
    }
}
