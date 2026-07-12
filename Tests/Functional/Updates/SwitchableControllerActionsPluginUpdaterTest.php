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
     * TCA constructed for this test). This is unchanged by 30e771a (sibling branch): it only wraps
     * the same lookup chain in is_array()/is_string() guards to silence a PHP "illegal string
     * offset" warning, without altering the resulting empty return value.
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
     * executeUpdate() as coded, unaffected by 30e771a.
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
