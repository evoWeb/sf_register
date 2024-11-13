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

namespace Evoweb\SfRegister\Tests\Functional;

use Evoweb\SfRegister\Tests\Functional\Http\ShortCircuitHandler;
use Evoweb\SfRegister\Tests\Functional\Http\ShortCircuitResponse;
use Evoweb\SfRegister\Tests\Functional\Traits\SiteBasedTestTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\Middleware\FrontendUserAuthenticator;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractTestBase extends FunctionalTestCase
{
    use SiteBasedTestTrait;

    /**
     * @var array<non-empty-string>
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sf_register',
        'typo3conf/ext/sf_register/Tests/Fixtures/Extensions/test_classes',
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

    protected ServerRequestInterface $request;

    public function initializeRequest(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $this->request = $serverRequestFactory
            ->createServerRequest('GET', '/')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    /**
     * @param array<string, mixed> $setup
     */
    public function initializeFrontendTypoScript(array $setup = []): void
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray($setup);
        $this->request = $this->request->withAttribute('frontend.typoscript', $frontendTypoScript);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function loginFrontendUser(string $username, string $password): void
    {
        // needed to ignore missing request token
        $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysAuthUser'] = true;

        $this->request = $this->request
            ->withQueryParams([
                ...$this->request->getQueryParams(),
                'logintype' => LoginType::LOGIN->value
            ])
            ->withParsedBody([
                ...((array) ($this->request->getParsedBody() ?? [])),
                'user' => $username,
                'pass' => $password,
            ]);

        $this->request = $this->processLocalMiddleWareStack($this->request);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function createEmptyFrontendUser(): void
    {
        $this->request = $this->processLocalMiddleWareStack($this->request);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    protected function processLocalMiddleWareStack(ServerRequestInterface $request): ServerRequestInterface
    {
        $shortCircuitHandler = new ShortCircuitHandler();
        /** @var FrontendUserAuthenticator $frontendUserAuthenticator */
        $frontendUserAuthenticator = $this->get(FrontendUserAuthenticator::class);
        /** @var ShortCircuitResponse $response */
        $response = $frontendUserAuthenticator->process($request, $shortCircuitHandler);
        return $response->getRequest();
    }

    public function getPrivateMethod(object $object, string $methodName): \ReflectionMethod
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getMethod($methodName);
    }

    public function getPrivateProperty(object $object, string $propertyName): \ReflectionProperty
    {
        $classReflection = new \ReflectionClass($object);
        return $classReflection->getProperty($propertyName);
    }
}
