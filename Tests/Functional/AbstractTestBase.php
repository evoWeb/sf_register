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

namespace Evoweb\SfRegister\Tests\Functional;

use Evoweb\SfRegister\Tests\Functional\Http\ShortCircuitKernel;
use Evoweb\SfRegister\Tests\Functional\Http\ShortCircuitResponse;
use Evoweb\SfRegister\Tests\Functional\Traits\SiteBasedTestTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\MiddlewareDispatcher;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Frontend\Http\Application;
use TYPO3\CMS\Frontend\Middleware\FrontendUserAuthenticator;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractTestBase extends FunctionalTestCase
{
    use SiteBasedTestTrait;

    protected string $instancePath = '';

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sf_register',
    ];

    /**
     * @var array<string, array<string, string|int>>
     */
    protected const LANGUAGE_PRESETS = [
        'EN' => [
            'id' => 0,
            'title' => 'English',
            'locale' => 'en_US.UTF8',
        ],
    ];

    protected array $loginMiddleWareStack = [
        'typo3/cms-frontend/authentication' => FrontendUserAuthenticator::class,
    ];

    protected ServerRequestInterface $request;

    public function initializeRequest(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $this->request = $serverRequestFactory->createServerRequest('GET', '/');
        $this->request = $this->request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $this->request = $this->request->withAttribute('extbase', new ExtbaseRequestParameters());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function initializeFrontendTypoScript(array $setup = []): void
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray($setup);
        $this->request = $this->request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function loginFrontEndUser(int $frontEndUserUid): void
    {
        if ($frontEndUserUid === 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1334439475);
        }

        $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysAuthUser'] = true;

        $this->request = $this->request
            ->withQueryParams([
                ...$this->request->getQueryParams(),
                'logintype' => LoginType::LOGIN->value
            ])
            ->withParsedBody([
                ...($this->request->getParsedBody() ?? []),
                'user' => 'testuser',
                'pass' => 'TestPa$5',
            ]);

        $this->request = $this->processLocalMiddleWareStack($this->request, $this->loginMiddleWareStack);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function createEmptyFrontendUser(): void
    {
        $this->request = $this->processLocalMiddleWareStack($this->request, $this->loginMiddleWareStack);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    protected function processLocalMiddleWareStack(
        ServerRequestInterface $request,
        array $subRequestMiddlewares
    ): ServerRequestInterface {
        $application = new Application(
            new MiddlewareDispatcher(
                new ShortCircuitKernel(),
                $subRequestMiddlewares,
                $this->getContainer(),
            ),
            $this->get(Context::class),
        );
        /** @var ShortCircuitResponse $response */
        $response = $application->handle($request);
        return $response->getRequest();
    }

    public function getPrivateMethod($object, $methodName): \ReflectionMethod
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getMethod($methodName);
    }

    public function getPrivateProperty($object, $propertyName): \ReflectionProperty
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getProperty($propertyName);
    }
}
