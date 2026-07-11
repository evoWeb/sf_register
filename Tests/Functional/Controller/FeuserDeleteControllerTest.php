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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\View\RecordingView;
use EvowebTests\TestClasses\Controller\FeuserDeleteController;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserDeleteControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();
    }

    /**
     * configurationManager is a shared object and will be a constructor parameter of the
     * controller (@see Bootstrap::initializeConfiguration).
     *
     * @param array<string, mixed> $arguments
     */
    protected function getSubject(
        string $controllerActionName,
        array $arguments = [],
    ): FeuserDeleteController {
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Delete',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Delete');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserDelete');
        $extbaseAttribute->setControllerActionName($controllerActionName);
        foreach ($arguments as $name => $value) {
            $extbaseAttribute->setArgument($name, $value);
        }

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        /** @var FeuserDeleteController $subject */
        $subject = $this->get(FeuserDeleteController::class);
        $subject->set('request', $request);
        $subject->set('actionMethodName', $controllerActionName);
        $subject->set('view', new RecordingView());

        return $subject;
    }

    /**
     * saveAction()/redirect() reads $this->uriBuilder directly (an ActionController property
     * that is normally initialized in processRequest(), which these tests bypass by calling
     * the action method directly). Swap in a recording test double that skips real frontend
     * link resolution (which would require a full site/TSFE setup unrelated to the logic under
     * test) while still returning a deterministic URI.
     */
    protected function mockUriBuilderForRedirect(FeuserDeleteController $subject): void
    {
        $uriBuilder = $this->createMock(UriBuilder::class);
        $uriBuilder->method('reset')->willReturnSelf();
        $uriBuilder->method('setCreateAbsoluteUri')->willReturnSelf();
        $uriBuilder->method('setTargetPageUid')->willReturnSelf();
        $uriBuilder->method('setAbsoluteUriScheme')->willReturnSelf();
        $uriBuilder->method('uriFor')->willReturn('https://typo3-testing.local/form');

        $subject->set('uriBuilder', $uriBuilder);
    }

    // -- formAction ---------------------------------------------------------------------------

    #[Test]
    public function formActionAssignsGivenUserToView(): void
    {
        $subject = $this->getSubject('form');
        $user = new FrontendUser();

        $response = $subject->formAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    // -- saveAction -----------------------------------------------------------------------------

    #[Test]
    public function saveActionRedirectsToFormWhenUserIsNull(): void
    {
        $subject = $this->getSubject('save');
        $this->mockUriBuilderForRedirect($subject);

        $response = $subject->saveAction(null);

        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    #[Test]
    public function saveActionSendsEmailAndAssignsUserWhenUserIsFoundByUid(): void
    {
        $subject = $this->getSubject('save');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);

        $response = $subject->saveAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertArrayNotHasKey('userNotFound', $view->variables);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function saveActionAssignsUserNotFoundWhenUserCannotBeResolvedByEmail(): void
    {
        $subject = $this->getSubject('save');
        $user = new FrontendUser();
        $user->setEmail('unknown@example.com');

        $response = $subject->saveAction($user);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertTrue($view->variables['userNotFound']);
        self::assertNull($view->variables['user']);
        self::assertInstanceOf(HtmlResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    // -- confirmAction --------------------------------------------------------------------------

    #[Test]
    public function confirmActionAssignsUserAlreadyDeletedWhenUserCannotBeDetermined(): void
    {
        $subject = $this->getSubject('confirm');

        $response = $subject->confirmAction(null, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame(1, $view->variables['userAlreadyDeleted']);
        self::assertInstanceOf(HtmlResponse::class, $response);
    }

    #[Test]
    public function confirmActionDeletesUserFromRepositoryAndAssignsUserDeleted(): void
    {
        $subject = $this->getSubject('confirm');
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid(1);
        self::assertSame(0, $user->getImage()->count());

        $response = $subject->confirmAction($user, null);

        /** @var RecordingView $view */
        $view = $subject->get('view');
        self::assertSame($user, $view->variables['user']);
        self::assertSame(1, $view->variables['userDeleted']);
        self::assertInstanceOf(HtmlResponse::class, $response);

        // userRepository->remove() + persistAll() run unconditionally on this branch; fe_users
        // has a "deleted" TCA flag column, so Extbase's persistence backend marks the row as
        // deleted (soft delete) instead of issuing a hard SQL DELETE.
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();
        $row = $queryBuilder
            ->select('deleted')
            ->from('fe_users')
            ->where('uid = 1')
            ->executeQuery()
            ->fetchAssociative();
        self::assertIsArray($row);
        self::assertEquals(1, $row['deleted']);
    }
}
