<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\ViewHelpers;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\ViewHelpers\RecordsViewHelper;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request as ExtbaseRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * RecordsViewHelper::render() resolves `table`/`uids` arguments and delegates to
 * getRecordsFromTable(), which queries the given table for the given uids (applying
 * the DeletedRestriction) and returns them ordered by uid ascending:
 *
 *   $result = $queryBuilder->select('*')->from($table)
 *       ->where($queryBuilder->expr()->in('uid', ...$uids...))
 *       ->orderBy('uid')
 *       ->executeQuery();
 *   return $result->fetchAllAssociative();
 *
 * render() guards $table/$uids with `is_string()` checks (dead code for any
 * template-driven invocation: both arguments are registered as required `string` via
 * registerArgument(), so Fluid's own argument validation already guarantees they arrive
 * as strings) and a `$table !== '' && $uids !== [] ? $this->getRecordsFromTable(...) : []`
 * guard. The latter is a reachable case: `table=""` is a perfectly valid value for a
 * *required* string argument (required only means "present", not "non-empty"). Without
 * the guard, an empty table name would be passed straight into getRecordsFromTable(),
 * which calls connectionPool->getQueryBuilderForTable('') - which itself rejects an
 * empty table name with an UnexpectedValueException. See rendersEmptyArrayWhenTableIsEmpty()
 * below.
 */
class RecordsViewHelperTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/records_pages.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        $this->writeSiteConfiguration(
            'test',
            $this->buildSiteConfiguration(1, 'https://example.org/'),
        );
    }

    /**
     * @param array<string, mixed> $variables
     */
    protected function renderTemplate(string $template, array $variables = []): string
    {
        $this->request = $this->request
            ->withAttribute('extbase', $this->createMock(ExtbaseRequestParameters::class));
        $extbaseRequest = new ExtbaseRequest($this->request);
        $extbaseRequest = $extbaseRequest
            ->withAttribute('currentContentObject', $this->get(ContentObjectRenderer::class));

        $renderingContextFactory = $this->get(RenderingContextFactory::class);
        self::assertInstanceOf(RenderingContextFactory::class, $renderingContextFactory);
        $context = $renderingContextFactory->create();
        $context->setAttribute(ServerRequestInterface::class, $extbaseRequest);
        $context->getTemplatePaths()->setTemplateSource(
            '{namespace register=Evoweb\SfRegister\ViewHelpers}' . $template
        );
        foreach ($variables as $name => $value) {
            $context->getVariableProvider()->add($name, $value);
        }
        $actual = (new TemplateView($context))->render();
        self::assertIsString($actual);
        return $actual;
    }

    /**
     * getRecordsFromTable(): the fixture table has rows for uid 10 (Alpha), 11 (Beta),
     * 12 (Charlie, not requested) and 13 (Gamma, soft-deleted). Requesting uids in
     * descending order ("11,10") still yields uid 10 before uid 11 in the result -
     * proving the query's `orderBy('uid')` (not the caller-supplied uids order)
     * determines the result order.
     */
    #[Test]
    public function getRecordsFromTableReturnsRequestedRecordsOrderedByUidRegardlessOfRequestOrder(): void
    {
        $subject = $this->get(RecordsViewHelper::class);
        self::assertInstanceOf(RecordsViewHelper::class, $subject);
        $subject->setArguments(['table' => 'pages', 'uids' => '11,10']);

        $result = $subject->render();

        self::assertSame([10, 11], array_column($result, 'uid'));
        self::assertSame(['Alpha', 'Beta'], array_column($result, 'title'));
    }

    /**
     * getRecordsFromTable(): uid 12 ("Charlie") exists in the fixture table but is not
     * part of the requested uids - it must not appear in the result, proving the
     * `uid IN (...)` filter excludes records outside the requested list.
     */
    #[Test]
    public function getRecordsFromTableExcludesRecordsNotInTheRequestedUidList(): void
    {
        $subject = $this->get(RecordsViewHelper::class);
        self::assertInstanceOf(RecordsViewHelper::class, $subject);
        $subject->setArguments(['table' => 'pages', 'uids' => '10,11']);

        $result = $subject->render();

        self::assertCount(2, $result);
        self::assertNotContains(12, array_column($result, 'uid'));
    }

    /**
     * getRecordsFromTable(): uid 13 ("Gamma") is soft-deleted (deleted=1) in the
     * fixture. Even though it is explicitly requested, the DeletedRestriction excludes
     * it from the result - proving the query applies that restriction on top of the
     * uid filter.
     */
    #[Test]
    public function getRecordsFromTableExcludesSoftDeletedRecordsEvenWhenRequested(): void
    {
        $subject = $this->get(RecordsViewHelper::class);
        self::assertInstanceOf(RecordsViewHelper::class, $subject);
        $subject->setArguments(['table' => 'pages', 'uids' => '10,11,13']);

        $result = $subject->render();

        self::assertSame([10, 11], array_column($result, 'uid'));
        self::assertNotContains(13, array_column($result, 'uid'));
    }

    /**
     * render(): the `is_array($this->arguments['uids'])` branch (uids supplied as an
     * already-built int[] instead of a comma-separated string) is exercised directly
     * via setArguments() - it produces the same filtered/ordered result as the
     * string-uids scenarios above.
     */
    #[Test]
    public function renderAcceptsUidsAsAnArrayInAdditionToACommaSeparatedString(): void
    {
        $subject = $this->get(RecordsViewHelper::class);
        self::assertInstanceOf(RecordsViewHelper::class, $subject);
        $subject->setArguments(['table' => 'pages', 'uids' => [13, 11, 10]]);

        $result = $subject->render();

        self::assertSame([10, 11], array_column($result, 'uid'));
    }

    /**
     * render() + Fluid rendering: the ViewHelper is invoked through an actual Fluid
     * template (inline call as the `each` expression of f:for), proving the tag
     * integration end-to-end - argument parsing, delegation to getRecordsFromTable(),
     * and iteration over the returned records.
     */
    #[Test]
    public function rendersTheFilteredAndOrderedRecordsThroughAFluidTemplate(): void
    {
        $actual = $this->renderTemplate(
            '<f:for each="{register:records(table: \'pages\', uids: \'11,10,13\')}" as="record">'
            . '{record.uid}:{record.title}' . "\n"
            . '</f:for>'
        );

        self::assertSame("10:Alpha\n11:Beta\n", $actual);
    }

    /**
     * render(): with an empty `table` argument, render() returns an empty array without
     * touching the database at all (the `$table !== '' && $uids !== []` guard). Without
     * that guard, render() would call getRecordsFromTable('', $uids), which calls
     * $this->connectionPool->getQueryBuilderForTable('') - and an empty table name is
     * rejected right there with an UnexpectedValueException (before any SQL is built or
     * executed).
     */
    #[Test]
    public function rendersEmptyArrayWhenTableIsEmpty(): void
    {
        // An empty table name short-circuits to [] (the `$table !== '' && $uids !== []` guard) instead
        // of reaching ConnectionPool::getQueryBuilderForTable('') and throwing UnexpectedValueException.
        $subject = $this->get(RecordsViewHelper::class);
        self::assertInstanceOf(RecordsViewHelper::class, $subject);
        $subject->setArguments(['table' => '', 'uids' => '10']);

        self::assertSame([], $subject->render());
    }
}
