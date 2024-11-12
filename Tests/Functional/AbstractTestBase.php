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

use Evoweb\SfRegister\Tests\Functional\Traits\SiteBasedTestTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
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

    protected ?FrontendUserAuthentication $frontendUser = null;

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

        $this->frontendUser = new FrontendUserAuthentication();
        $this->frontendUser->setLogger(new NullLogger());
        $this->frontendUser->start($this->request);
        $this->frontendUser->user = $this->frontendUser->getRawUserByUid($frontEndUserUid);
        $this->frontendUser->fetchGroupData($this->request);

        if (isset($this->frontendUser->user['uc'])) {
            $theUC = unserialize($this->frontendUser->user['uc'], ['allowed_classes' => false]);
            if (is_array($theUC)) {
                $this->frontendUser->uc = $theUC;
            }
        }

        $userAspect = $this->frontendUser->createUserAspect();

        /** @var Context $context */
        $context = $this->get(Context::class);
        $context->setAspect('frontend.user', $userAspect);
        $this->request = $this->request->withAttribute('frontend.user', $this->frontendUser);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    public function createEmptyFrontendUser(): void
    {
        $this->frontendUser = new FrontendUserAuthentication();

        /** @var UserAspect $aspect */
        $aspect = GeneralUtility::makeInstance(UserAspect::class, $this->frontendUser);

        /** @var Context $context */
        $context = $this->get(Context::class);
        $context->setAspect('frontend.user', $aspect);
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
