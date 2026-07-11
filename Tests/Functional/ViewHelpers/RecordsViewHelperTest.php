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
 * git show 30e771a (phpstan fix) on this class - what ACTUALLY changed, vs. the task
 * brief's claim that getRecordsFromTable/initializeArguments changed:
 *
 * - initializeArguments(): UNCHANGED by 30e771a (confirmed via `git show 30e771a`).
 *   The brief is wrong to list it as a changed method (same kind of mislabeling seen on
 *   task 16). It still just registers the required `table` (string) and `uids`
 *   (string) arguments - exercised implicitly by every render() call below.
 * - render(): DID change substantially, though it is NOT listed by the brief at all.
 *   Pre-fix: `$table = $this->arguments['table'];` and
 *   `$uids = is_array(...) ? ... : GeneralUtility::intExplode(',', $this->arguments['uids']);`,
 *   then unconditionally `return $this->getRecordsFromTable($table, $uids);`.
 *   Post-fix adds `is_string()` guards around $table/$uids (defensive phpstan
 *   type-narrowing - both arguments are registered as required `string` via
 *   registerArgument(), so Fluid's own argument validation already guarantees they
 *   arrive as strings for any template-driven invocation; the array branch for `uids`
 *   is untouched. Dead code for real invocations, exercised implicitly, no observable
 *   difference for the scenarios below) AND a genuinely new guard:
 *   `return $table !== '' && $uids !== [] ? $this->getRecordsFromTable(...) : [];`.
 *   This last part IS a reachable behavior change: `table=""` is a perfectly valid
 *   value for a *required* string argument (required only means "present", not
 *   "non-empty"). Pre-fix, an empty table name is passed straight into
 *   getRecordsFromTable(), which calls connectionPool->getQueryBuilderForTable('') -
 *   which itself rejects an empty table name with an UnexpectedValueException; post-fix
 *   short-circuits to `[]` before ever touching the database. See
 *   rendersAnEmptyArrayInsteadOfThrowingWhenTableIsEmpty() below - this is a genuine
 *   pre-fix bug (Bug-Protokoll skip), verified RED.
 * - getRecordsFromTable(): DID change, but only inside the catch block wrapping
 *   `$result->fetchAllAssociative()`:
 *   `$exception->getPrevious()->getMessage()` (pre-fix) -> `$exception->getMessage()`
 *   (post-fix). The query building/where/orderBy logic that determines *which* records
 *   come back and in *what order* is completely untouched by 30e771a - it is
 *   characterized (not "fixed") by the tests below. The catch block itself sits around
 *   fetchAllAssociative() only; a bogus/invalid table name throws from executeQuery()
 *   one line above the try, i.e. outside this catch entirely, so this diff line is not
 *   reachable through normal record-fetching scenarios (no realistic functional-test
 *   fixture makes fetchAllAssociative() itself throw after a successful executeQuery())
 *   - not exercised here, no skip needed (untestable dead corner via this path, not a
 *   confirmed-safe divergence, but out of reach for a fixture-driven functional test).
 *
 * Net result: getRecordsFromTable's actual query behavior (filter by requested uids,
 * apply DeletedRestriction, order by uid) is unchanged by 30e771a and characterized
 * directly below. The one reachable, in-scope divergence (render()'s empty-table
 * guard) is documented as a Bug-Protokoll skip.
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
     * render(): with an empty `table` argument, the SOLL behavior (post-30e771a) is to
     * return an empty array without touching the database at all - see the
     * `$table !== '' && $uids !== []` guard added in 30e771a.
     *
     * Pre-fix (df53334), render() unconditionally calls
     * getRecordsFromTable('', $uids), which calls
     * $this->connectionPool->getQueryBuilderForTable('') - and an empty table name is
     * rejected right there (before any SQL is built or executed). Confirmed RED by
     * temporarily un-skipping this test:
     *
     *   1) Evoweb\SfRegister\Tests\Functional\ViewHelpers\RecordsViewHelperTest::rendersAnEmptyArrayInsteadOfThrowingWhenTableIsEmpty
     *   UnexpectedValueException: ConnectionPool->getQueryBuilderForTable() requires a
     *   connection name to be provided.
     *
     *   /vendor/typo3/cms-core/Classes/Database/ConnectionPool.php:421
     *   /Classes/ViewHelpers/RecordsViewHelper.php:62 (getRecordsFromTable)
     *   /Classes/ViewHelpers/RecordsViewHelper.php:53 (render)
     *
     * This is a genuine pre-fix bug in the very method under test (render(), which
     * delegates directly to getRecordsFromTable()) - Bug-Protokoll skip, reactivate in
     * Roadmap-Schritt 2 once 30e771a's render() guard is in place.
     */
    #[Test]
    public function rendersAnEmptyArrayInsteadOfThrowingWhenTableIsEmpty(): void
    {
        self::markTestSkipped(
            'Pre-fix bug in df53334: render() calls getRecordsFromTable(\'\', $uids) '
            . 'unconditionally, which throws an UnexpectedValueException from '
            . 'ConnectionPool::getQueryBuilderForTable(\'\') instead of returning []. '
            . 'Behoben in 30e771a (Classes/ViewHelpers/RecordsViewHelper::render). '
            . 'Reaktivieren in Roadmap-Schritt 2.'
        );

        // $subject = $this->get(RecordsViewHelper::class);
        // self::assertInstanceOf(RecordsViewHelper::class, $subject);
        // $subject->setArguments(['table' => '', 'uids' => '10']);
        //
        // self::assertSame([], $subject->render());
    }
}
