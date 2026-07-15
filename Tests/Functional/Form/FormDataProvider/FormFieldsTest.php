<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Tests\Functional\Form\FormDataProvider;

use Evoweb\SfRegister\Form\FormDataProvider\FormFields;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;

/**
 * FormFields is a backend FormDataProvider that resolves the select items and
 * the pre-selected values for TCA columns flagged with config.sfRegisterForm:
 *
 *   addData(): for every processedTca column having config.sfRegisterForm set,
 *     - replaces the column config with getAvailableFields() output, which adds
 *       config.items built from getAvailableFieldsFromTsConfig() (label/value
 *       pairs for every truthy entry of
 *       plugin.tx_sfregister.settings.fields.configuration. in the backend
 *       user's TSconfig),
 *     - and, only if the databaseRow has no (or an empty) value for that field
 *       yet and config.doNotPreSelect is not set, seeds databaseRow[field] with
 *       getSelectedFields(sfRegisterForm), which is
 *       getDefaultSelectedFieldsFromTsConfig()[sfRegisterForm . '.'] ?? [] -
 *       i.e. plugin.tx_sfregister.settings.fields.defaultSelected.<formType>.
 *
 * addData() defensively guards processedTca/databaseRow with is_array()/continue
 * checks, and getAvailableFieldsFromTsConfig()/getDefaultSelectedFieldsFromTsConfig()
 * null-safe getBackendUserAuthentication()?->getTSConfig(). Neither path is
 * reachable in production: FormEngine's DataProvider chain always populates
 * processedTca.columns/databaseRow as well-formed arrays before this provider
 * runs, and $GLOBALS['BE_USER'] is always an authenticated
 * BackendUserAuthentication instance inside a bootstrapped backend FormEngine
 * request (see LanguageKeyViewHelperTest for the identical
 * getBackendUserAuthentication() reasoning). Tests below therefore only
 * exercise well-formed input with a BE_USER double; no malformed-input/
 * null-BE_USER test is added since that shape is never reachable from real
 * FormEngine use.
 */
class FormFieldsTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        $languageServiceFactory = $this->get(LanguageServiceFactory::class);
        self::assertInstanceOf(LanguageServiceFactory::class, $languageServiceFactory);
        $GLOBALS['LANG'] = $languageServiceFactory->create('en');
    }

    /**
     * Installs a BackendUserAuthentication double as $GLOBALS['BE_USER'] whose
     * getTSConfig() returns the given array, mirroring how
     * FunctionalTestCase::setUpBackendUser() populates $GLOBALS['BE_USER'] but
     * without needing a be_users fixture, since FormFields only ever calls
     * getTSConfig() on it.
     *
     * @param array<string, mixed> $tsConfig
     */
    protected function setBackendUserTsConfig(array $tsConfig): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->method('getTSConfig')->willReturn($tsConfig);
        $GLOBALS['BE_USER'] = $backendUser;
    }

    protected function getSubject(): FormFields
    {
        $subject = $this->get(FormFields::class);
        self::assertInstanceOf(FormFields::class, $subject);
        return $subject;
    }

    /**
     * Narrows the mixed-typed nested offsets of addData()'s
     * array<string, mixed> return value down to the column config array for
     * assertions, verifying the array shape on the way.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    protected function getColumnConfig(array $result, string $column): array
    {
        $processedTca = $result['processedTca'];
        self::assertIsArray($processedTca);
        $columns = $processedTca['columns'];
        self::assertIsArray($columns);
        $columnData = $columns[$column];
        self::assertIsArray($columnData);
        $config = $columnData['config'];
        self::assertIsArray($config);
        return $config;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    protected function getDatabaseRow(array $result): array
    {
        $databaseRow = $result['databaseRow'];
        self::assertIsArray($databaseRow);
        return $databaseRow;
    }

    /**
     * @param array<string, mixed> $sfRegisterFormFieldConfig
     * @param array<string, mixed> $databaseRow
     * @return array<string, mixed>
     */
    protected function buildResult(array $sfRegisterFormFieldConfig, array $databaseRow = []): array
    {
        return [
            'tableName' => 'fe_users',
            'databaseRow' => $databaseRow,
            'processedTca' => [
                'columns' => [
                    'plainField' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                    'myField' => [
                        'config' => array_merge(
                            ['type' => 'select'],
                            $sfRegisterFormFieldConfig
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sampleTsConfig(): array
    {
        return [
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'fields.' => [
                            'configuration.' => [
                                'title.' => [
                                    'partial' => 'Select',
                                    'backendLabel' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.title',
                                ],
                                'firstName.' => [
                                    'partial' => 'Textfield',
                                ],
                            ],
                            'defaultSelected.' => [
                                'create.' => [
                                    '10' => 'firstName',
                                    '20' => 'title',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * getAvailableFieldsFromTsConfig() returns the
     * plugin.tx_sfregister.settings.fields.configuration. sub-array of the
     * backend user's TSconfig verbatim.
     */
    #[Test]
    public function getAvailableFieldsFromTsConfigReturnsTheConfiguredFieldsSubArray(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getAvailableFieldsFromTsConfig');

        self::assertSame(
            [
                'title.' => [
                    'partial' => 'Select',
                    'backendLabel' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.title',
                ],
                'firstName.' => [
                    'partial' => 'Textfield',
                ],
            ],
            $method->invoke($subject)
        );
    }

    /**
     * getAvailableFieldsFromTsConfig() falls back to an empty array when the
     * backend user has no plugin.tx_sfregister. TSconfig at all.
     */
    #[Test]
    public function getAvailableFieldsFromTsConfigReturnsEmptyArrayWhenTsConfigIsEmpty(): void
    {
        $this->setBackendUserTsConfig([]);
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getAvailableFieldsFromTsConfig');

        self::assertSame([], $method->invoke($subject));
    }

    /**
     * getDefaultSelectedFieldsFromTsConfig() returns the
     * plugin.tx_sfregister.settings.fields.defaultSelected. sub-array of the
     * backend user's TSconfig verbatim.
     */
    #[Test]
    public function getDefaultSelectedFieldsFromTsConfigReturnsTheDefaultSelectedSubArray(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getDefaultSelectedFieldsFromTsConfig');

        self::assertSame(
            [
                'create.' => [
                    '10' => 'firstName',
                    '20' => 'title',
                ],
            ],
            $method->invoke($subject)
        );
    }

    /**
     * getDefaultSelectedFieldsFromTsConfig() falls back to an empty array when
     * the backend user has no plugin.tx_sfregister. TSconfig at all.
     */
    #[Test]
    public function getDefaultSelectedFieldsFromTsConfigReturnsEmptyArrayWhenTsConfigIsEmpty(): void
    {
        $this->setBackendUserTsConfig([]);
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getDefaultSelectedFieldsFromTsConfig');

        self::assertSame([], $method->invoke($subject));
    }

    /**
     * getSelectedFields(string $formType) returns the defaultSelected list for
     * that form type, keyed by the original TSconfig sorting position.
     */
    #[Test]
    public function getSelectedFieldsReturnsTheDefaultSelectedListForTheGivenFormType(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSelectedFields');

        self::assertSame(
            ['10' => 'firstName', '20' => 'title'],
            $method->invoke($subject, 'create')
        );
    }

    /**
     * getSelectedFields(string $formType) falls back to an empty array for a
     * form type that has no defaultSelected configuration.
     */
    #[Test]
    public function getSelectedFieldsReturnsEmptyArrayForAnUnconfiguredFormType(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSelectedFields');

        self::assertSame([], $method->invoke($subject, 'unconfigured'));
    }

    /**
     * addData() leaves columns without config.sfRegisterForm completely
     * untouched (the `continue` guard).
     */
    #[Test]
    public function addDataLeavesColumnsWithoutSfRegisterFormUntouched(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $result = $this->buildResult(['sfRegisterForm' => 'create']);

        $actual = $subject->addData($result);

        self::assertSame(
            ['type' => 'input'],
            $this->getColumnConfig($actual, 'plainField')
        );
    }

    /**
     * addData() builds config.items from getAvailableFieldsFromTsConfig() for
     * a column with config.sfRegisterForm set, translating each entry's
     * backendLabel (or falling back to and resolving
     * 'sf_register.be:fe_users.<field>' via the extension's own
     * locallang_be.xlf domain when no backendLabel is set), and pre-selects
     * databaseRow[field] with getSelectedFields(sfRegisterForm) because
     * databaseRow has no value yet for that field.
     */
    #[Test]
    public function addDataBuildsItemsAndPreSelectsDefaultFieldsWhenDatabaseRowIsEmpty(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $result = $this->buildResult(['sfRegisterForm' => 'create']);

        $actual = $subject->addData($result);
        $config = $this->getColumnConfig($actual, 'myField');
        $databaseRow = $this->getDatabaseRow($actual);

        self::assertSame(
            [
                ['label' => 'Title', 'value' => 'title'],
                ['label' => 'First name', 'value' => 'firstName'],
            ],
            $config['items']
        );
        self::assertSame(
            ['10' => 'firstName', '20' => 'title'],
            $databaseRow['myField']
        );
    }

    /**
     * addData() does not touch databaseRow[field] when it already carries a
     * non-empty value - the pre-select branch is guarded by
     * processDatabaseFieldValue() returning a non-empty array.
     */
    #[Test]
    public function addDataDoesNotOverwriteAnAlreadySelectedDatabaseRowValue(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $result = $this->buildResult(
            ['sfRegisterForm' => 'create'],
            ['myField' => 'title']
        );

        $actual = $subject->addData($result);
        $databaseRow = $this->getDatabaseRow($actual);

        self::assertSame('title', $databaseRow['myField']);
    }

    /**
     * addData() does not pre-select databaseRow[field] when
     * config.doNotPreSelect is set, even though the row has no value yet -
     * but it still builds config.items.
     */
    #[Test]
    public function addDataDoesNotPreSelectWhenDoNotPreSelectIsSet(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $result = $this->buildResult(['sfRegisterForm' => 'create', 'doNotPreSelect' => true]);

        $actual = $subject->addData($result);
        $config = $this->getColumnConfig($actual, 'myField');
        $databaseRow = $this->getDatabaseRow($actual);

        self::assertArrayNotHasKey('myField', $databaseRow);
        self::assertSame(
            [
                ['label' => 'Title', 'value' => 'title'],
                ['label' => 'First name', 'value' => 'firstName'],
            ],
            $config['items']
        );
    }

    /**
     * addData() pre-selects an empty array when the sfRegisterForm value has
     * no matching defaultSelected TSconfig at all.
     */
    #[Test]
    public function addDataPreSelectsEmptyArrayForAnUnconfiguredFormType(): void
    {
        $this->setBackendUserTsConfig($this->sampleTsConfig());
        $subject = $this->getSubject();
        $result = $this->buildResult(['sfRegisterForm' => 'unconfigured']);

        $actual = $subject->addData($result);
        $databaseRow = $this->getDatabaseRow($actual);

        self::assertSame([], $databaseRow['myField']);
    }
}
