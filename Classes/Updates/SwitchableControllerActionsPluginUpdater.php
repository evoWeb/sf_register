<?php

declare(strict_types=1);

/*
 * This file is part of the Extension "sf_register" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Updates;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception as DbalException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('sfrSwitchableControllerActionsPluginUpdater')]
class SwitchableControllerActionsPluginUpdater implements UpgradeWizardInterface, ChattyInterface
{
    private const TABLE_NAME = 'tt_content';

    private const MIGRATION_SETTINGS = [
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserCreate->form;FeuserCreate->preview;FeuserCreate->proxy;'
                . 'FeuserCreate->save;FeuserCreate->confirm;FeuserCreate->accept;FeuserCreate->decline;'
                . 'FeuserCreate->refuse;FeuserCreate->removeImage',
            'targetListType' => 'sfregister_create'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserEdit->form;FeuserEdit->preview;FeuserEdit->proxy;'
                . 'FeuserEdit->save;FeuserEdit->confirm;FeuserEdit->accept;FeuserEdit->removeImage',
            'targetListType' => 'sfregister_edit'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserPassword->form;FeuserPassword->save',
            'targetListType' => 'sfregister_password'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserInvite->form;FeuserInvite->invite',
            'targetListType' => 'sfregister_invite'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserDelete->form;FeuserDelete->save;FeuserDelete->confirm',
            'targetListType' => 'sfregister_delete'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserResend->form;FeuserResend->mail',
            'targetListType' => 'sfregister_resend'
        ],
    ];

    protected FlexFormService $flexFormService;

    protected OutputInterface $output;

    public function __construct()
    {
        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'Migrates plugin and settings of existing sf_register plugins using switchableControllerActions';
    }

    public function getDescription(): string
    {
        return 'The old sf_register plugin using switchableControllerActions has been split into six separate plugins.
            This update wizard migrates all existing plugin settings and changes the Plugin (list_type) to use the new
            plugins available.';
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    public function updateNecessary(): bool
    {
        try {
            $necessary = count($this->getRecordsToUpdate()) > 0;
        } catch (\Exception) {
            $necessary = 0;
        }
        return $necessary;
    }

    public function executeUpdate(): bool
    {
        try {
            $records = $this->getRecordsToUpdate();
        } catch (\Exception) {
            return false;
        }

        foreach ($records as $record) {
            if (!str_contains($record['pi_flexform'], 'switchableControllerActions')) {
                continue;
            }

            $flexForm = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
            $newListType = $this->getTargetListType($record['list_type'], $flexForm['switchableControllerActions']);
            $allowedSettings = $this->getSettingsFromFlexFormDataStructureFile($newListType);

            $flexFormData = GeneralUtility::xml2array($record['pi_flexform']);
            $flexFormData = $this->removeFieldsNotPresentInDataStructure($flexFormData, $allowedSettings);
            $newFlexform = count($flexFormData['data']) ? $this->transformArrayToXml($flexFormData) : '';

            $this->updateRecordWithNewListTypeAndFormData($record['uid'], $newListType, $newFlexform);
        }

        return true;
    }

    /**
     * @throws DbalException
     */
    protected function getRecordsToUpdate(): array
    {
        $checkListTypes = array_unique(array_column(self::MIGRATION_SETTINGS, 'sourceListType'));

        $queryBuilder = $this->getPreparedQueryBuilder();
        return $queryBuilder
            ->select('uid', 'list_type', 'pi_flexform')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->in(
                    'list_type',
                    $queryBuilder->createNamedParameter($checkListTypes, ArrayParameterType::STRING)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function getTargetListType(string $sourceListType, string $switchableControllerActions): string
    {
        $result = '';

        foreach (self::MIGRATION_SETTINGS as $setting) {
            if (
                $setting['sourceListType'] === $sourceListType
                && $setting['switchableControllerActions'] === $switchableControllerActions
            ) {
                $result = $setting['targetListType'];
                break;
            }
        }

        return $result;
    }

    protected function getSettingsFromFlexFormDataStructureFile(string $listType): array
    {
        $flexFormFile =
            $GLOBALS['TCA'][self::TABLE_NAME]['columns']['pi_flexform']['config']['ds'][$listType . ',list'] ?? null;

        if ($flexFormFile === null) {
            return [];
        }

        $flexFormContent = file_get_contents(GeneralUtility::getFileAbsFileName(substr(trim($flexFormFile), 5)));
        $flexFormData = GeneralUtility::xml2array($flexFormContent);

        $settings = [];
        foreach ($flexFormData['sheets'] as $sheet) {
            foreach ($sheet['ROOT']['el'] as $setting => $tceForms) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    protected function removeFieldsNotPresentInDataStructure(array $flexFormData, array $allowedSettings): array
    {
        foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
            foreach ($sheetData['lDEF'] as $settingName => $setting) {
                // Remove fields which do not exist in flexform data structure of new plugin
                if (!in_array($settingName, $allowedSettings, true)) {
                    unset($flexFormData['data'][$sheetKey]['lDEF'][$settingName]);
                }
            }

            // Remove empty sheets
            if (!count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                unset($flexFormData['data'][$sheetKey]);
            }
        }

        return $flexFormData;
    }

    protected function updateRecordWithNewListTypeAndFormData(int $uid, string $newListType, string $newFlexform): void
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $queryBuilder->update(self::TABLE_NAME)
            ->set('list_type', $newListType)
            ->set('pi_flexform', $newFlexform)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    /**
     * Transforms the flexform data array to FlexForm XML
     */
    protected function transformArrayToXml(array $input = []): string
    {
        $nsPrefix = '';
        $level = 0;
        $docTag = 'T3FlexForms';
        $spaceInd = 4;
        $options = [
            'parentTagMap' => [
                'data' => 'sheet',
                'sheet' => 'language',
                'language' => 'field',
                'el' => 'field',
                'field' => 'value',
                'field:el' => 'el',
                'el:_IS_NUM' => 'section',
                'section' => 'itemType'
            ],
            'disableTypeAttrib' => 2
        ];

        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'
            . LF
            . GeneralUtility::array2xml($input, $nsPrefix, $level, $docTag, $spaceInd, $options);
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->getRestrictions()
            ->removeAll();

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
