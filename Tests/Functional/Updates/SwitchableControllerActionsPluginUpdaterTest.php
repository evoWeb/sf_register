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

namespace Evoweb\SfRegister\Tests\Functional\Updates;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Updates\SwitchableControllerActionsPluginUpdater;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SwitchableControllerActionsPluginUpdaterTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getSubject(): SwitchableControllerActionsPluginUpdater
    {
        /** @var SwitchableControllerActionsPluginUpdater $subject */
        $subject = $this->get(SwitchableControllerActionsPluginUpdater::class);
        return $subject;
    }

    /**
     * Point the legacy per-list_type TCA `ds` entry at a real FlexForm DS file so the wizard can
     * resolve settings for the given list_type. On modern core `...config.ds` is core's default
     * XML *string*, so we overwrite the whole entry with an array (index-assigning into a string
     * throws). Functional tests reset $GLOBALS['TCA'] per test, so this mutation is isolated.
     */
    protected function injectFlexFormDataStructure(string $listType, string $dataStructureFile): void
    {
        // @phpstan-ignore-next-line -- offset write on the mixed-typed $GLOBALS['TCA'] super-global
        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'] = [
            $listType . ',list' => $dataStructureFile,
        ];
    }

    // -- getTargetListType ----------------------------------------------------------------------

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function targetListTypeDataProvider(): array
    {
        return [
            'FeuserCreate actions map to sfregister_create' => [
                'sfregister_form',
                'FeuserCreate->form;FeuserCreate->preview;FeuserCreate->proxy;'
                . 'FeuserCreate->save;FeuserCreate->confirm;FeuserCreate->accept;FeuserCreate->decline;'
                . 'FeuserCreate->refuse;FeuserCreate->removeImage',
                'sfregister_create',
            ],
            'FeuserEdit actions map to sfregister_edit' => [
                'sfregister_form',
                'FeuserEdit->form;FeuserEdit->preview;FeuserEdit->proxy;'
                . 'FeuserEdit->save;FeuserEdit->confirm;FeuserEdit->accept;FeuserEdit->removeImage',
                'sfregister_edit',
            ],
            'FeuserPassword actions map to sfregister_password' => [
                'sfregister_form',
                'FeuserPassword->form;FeuserPassword->save',
                'sfregister_password',
            ],
            'FeuserInvite actions map to sfregister_invite' => [
                'sfregister_form',
                'FeuserInvite->form;FeuserInvite->invite',
                'sfregister_invite',
            ],
            'FeuserDelete actions map to sfregister_delete' => [
                'sfregister_form',
                'FeuserDelete->form;FeuserDelete->save;FeuserDelete->confirm',
                'sfregister_delete',
            ],
            'FeuserResend actions map to sfregister_resend' => [
                'sfregister_form',
                'FeuserResend->form;FeuserResend->mail',
                'sfregister_resend',
            ],
            'unknown switchableControllerActions do not resolve to a target list_type' => [
                'sfregister_form',
                'FeuserCreate->form',
                '',
            ],
            'known switchableControllerActions with unknown source list_type do not resolve' => [
                'someother_plugin',
                'FeuserCreate->form;FeuserCreate->preview;FeuserCreate->proxy;'
                . 'FeuserCreate->save;FeuserCreate->confirm;FeuserCreate->accept;FeuserCreate->decline;'
                . 'FeuserCreate->refuse;FeuserCreate->removeImage',
                '',
            ],
        ];
    }

    #[DataProvider('targetListTypeDataProvider')]
    #[Test]
    public function getTargetListTypeReturnsExpectedListType(
        string $sourceListType,
        string $switchableControllerActions,
        string $expected,
    ): void {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getTargetListType');

        self::assertSame($expected, $method->invoke($subject, $sourceListType, $switchableControllerActions));
    }

    // -- getSettingsFromFlexFormDataStructureFile ------------------------------------------------

    /**
     * The method reads the FlexForm DS file path from the legacy TCA path
     * $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][$listType . ',list'].
     * On the TYPO3 core version loaded here (14/15), ExtensionUtility::registerPlugin()/addPlugin()
     * no longer populate that legacy per-list_type array; the DS reference for a plugin CType is
     * stored under $GLOBALS['TCA']['tt_content']['types'][$CType]['columnsOverrides']['pi_flexform']
     * ['config']['ds'] instead, and 'columns.pi_flexform.config.ds' itself is just core's fixed
     * default XML string (not an array). So the legacy lookup never finds anything for ANY
     * list_type on this core version - verified directly against the real, loaded TCA (no stub
     * TCA constructed for this test). The method wraps this lookup chain in is_array()/is_string()
     * guards to silence a PHP "illegal string offset" warning; the resulting return value is
     * still an empty array.
     */
    #[Test]
    public function getSettingsFromFlexFormDataStructureFileReturnsEmptyArrayForKnownListType(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSettingsFromFlexFormDataStructureFile');

        self::assertSame([], $method->invoke($subject, 'sfregister_create'));
    }

    #[Test]
    public function getSettingsFromFlexFormDataStructureFileReturnsEmptyArrayForUnknownListType(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSettingsFromFlexFormDataStructureFile');

        self::assertSame([], $method->invoke($subject, 'sfregister_unknown'));
    }

    /**
     * Drives the REAL parse path of the method (the is_array()-guarded sheets -> ROOT -> el loop):
     * we point the legacy TCA `ds` entry at a real FlexForm DS file that sf_register ships
     * (Configuration/FlexForms/create.xml, which has the exact sheets/sDEF/ROOT/el shape the loop
     * expects). The method then reads and parses that file and returns the `<el>` setting keys in
     * document order. Functional tests reset $GLOBALS['TCA'] per test, so mutating it here is
     * isolated to this test.
     */
    #[Test]
    public function getSettingsFromFlexFormDataStructureFileParsesRealDataStructureFile(): void
    {
        $listType = 'sfregister_create';
        $this->injectFlexFormDataStructure($listType, 'FILE:EXT:sf_register/Configuration/FlexForms/create.xml');

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getSettingsFromFlexFormDataStructureFile');

        self::assertSame(
            ['settings.fields.selected', 'settings.templateRootPath'],
            $method->invoke($subject, $listType)
        );
    }

    // -- executeUpdate ----------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    protected function fetchRecord(int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $row = $queryBuilder
            ->select('uid', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->executeQuery()
            ->fetchAssociative();

        self::assertIsArray($row);

        return $row;
    }

    /**
     * getSettingsFromFlexFormDataStructureFile() always returns [] on this TYPO3 core version
     * (see comment above), so removeFieldsNotPresentInDataStructure() strips every field from
     * every sheet (nothing is "allowed"), leaving pi_flexform rewritten to an empty string. The
     * list_type is still updated to the resolved target. This is the real, verified behavior of
     * executeUpdate() as coded.
     */
    #[Test]
    public function executeUpdateMigratesRecordListTypeAndEmptiesFlexformFields(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $subject = $this->getSubject();

        $result = $subject->executeUpdate();

        self::assertTrue($result);

        $record = $this->fetchRecord(1);
        self::assertSame('sfregister_create', $record['list_type']);
        self::assertSame('', $record['pi_flexform']);
    }

    /**
     * Drives executeUpdate()'s flexform-PRESERVATION branch: by injecting a real DS file for the
     * resolved target list_type, getSettingsFromFlexFormDataStructureFile() returns a NON-empty
     * allow-list (create.xml's settings.fields.selected + settings.templateRootPath), so
     * removeFieldsNotPresentInDataStructure() keeps those fields while dropping the
     * switchableControllerActions field and the disallowed settings.unknownField. The row is
     * migrated with list_type updated and a rewritten (non-empty) pi_flexform. This is the branch
     * the other executeUpdate tests cannot reach, because on modern core the DS path is absent and
     * the allow-list is always empty (see env note above).
     */
    #[Test]
    public function executeUpdatePreservesAllowedFlexformFieldsWhenDataStructureResolves(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        // So the resolved target list_type maps to a real DS file and the allow-list is non-empty.
        $this->injectFlexFormDataStructure(
            'sfregister_create',
            'FILE:EXT:sf_register/Configuration/FlexForms/create.xml'
        );

        $subject = $this->getSubject();

        $subject->executeUpdate();

        $record = $this->fetchRecord(1);
        self::assertSame('sfregister_create', $record['list_type']);
        $flexform = $record['pi_flexform'];
        self::assertIsString($flexform);
        self::assertNotSame('', $flexform);

        // Allowed settings (present in create.xml) survive the migration...
        self::assertStringContainsString('settings.fields.selected', $flexform);
        self::assertStringContainsString('settings.templateRootPath', $flexform);
        self::assertStringContainsString('firstName,lastName,email', $flexform);
        // ...while the SCA field and the disallowed field are dropped.
        self::assertStringNotContainsString('switchableControllerActions', $flexform);
        self::assertStringNotContainsString('settings.unknownField', $flexform);
        self::assertStringNotContainsString('shouldBeRemoved', $flexform);

        // Assert the concrete surviving lDEF field keys of the rewritten flexform.
        $decoded = GeneralUtility::xml2array($flexform);
        self::assertIsArray($decoded);
        $data = $decoded['data'] ?? null;
        self::assertIsArray($data);
        $sheet = $data['sDEF'] ?? null;
        self::assertIsArray($sheet);
        $lDEF = $sheet['lDEF'] ?? null;
        self::assertIsArray($lDEF);
        self::assertArrayHasKey('settings.fields.selected', $lDEF);
        self::assertArrayHasKey('settings.templateRootPath', $lDEF);
        self::assertArrayNotHasKey('switchableControllerActions', $lDEF);
        self::assertArrayNotHasKey('settings.unknownField', $lDEF);
    }

    #[Test]
    public function executeUpdateMigratesSecondRecordToItsOwnTargetListType(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $subject = $this->getSubject();

        $subject->executeUpdate();

        $record = $this->fetchRecord(2);
        self::assertSame('sfregister_delete', $record['list_type']);
        self::assertSame('', $record['pi_flexform']);
    }

    #[Test]
    public function executeUpdateLeavesRecordWithNonMatchingListTypeUntouched(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $before = $this->fetchRecord(3);
        $subject = $this->getSubject();

        $subject->executeUpdate();

        $after = $this->fetchRecord(3);
        self::assertSame($before['list_type'], $after['list_type']);
        self::assertSame($before['pi_flexform'], $after['pi_flexform']);
        self::assertSame('sfregister_edit', $after['list_type']);
    }

    #[Test]
    public function executeUpdateLeavesRecordWithoutSwitchableControllerActionsUntouched(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
        $before = $this->fetchRecord(4);
        $subject = $this->getSubject();

        $subject->executeUpdate();

        $after = $this->fetchRecord(4);
        self::assertSame($before['list_type'], $after['list_type']);
        self::assertSame($before['pi_flexform'], $after['pi_flexform']);
        self::assertSame('sfregister_form', $after['list_type']);
    }
}
