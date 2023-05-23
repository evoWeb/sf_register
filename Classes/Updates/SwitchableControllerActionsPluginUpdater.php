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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('sfrSwitchableControllerActionsPluginUpdater')]
class SwitchableControllerActionsPluginUpdater implements UpgradeWizardInterface
{
    private const MIGRATION_SETTINGS = [
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserCreate->form;FeuserCreate->preview;FeuserCreate->proxy;FeuserCreate->save;FeuserCreate->confirm;FeuserCreate->accept;FeuserCreate->decline;FeuserCreate->refuse;FeuserCreate->removeImage',
            'targetListType' => 'sfregister_create'
        ],
        [
            'sourceListType' => 'sfregister_form',
            'switchableControllerActions' => 'FeuserEdit->form;FeuserEdit->preview;FeuserEdit->proxy;FeuserEdit->save;FeuserEdit->confirm;FeuserEdit->accept;FeuserEdit->removeImage',
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
            'switchableControllerActions' => 'FeuserResend->form;FeuserResend->mail',
            'targetListType' => 'sfregister_resend'
        ],
    ];

    protected FlexFormService $flexFormService;

    public function __construct()
    {
        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
    }

    public function getTitle(): string
    {
        return 'Migrates plugin and settings of existing sf_register plugins using switchableControllerActions';
    }

    public function getDescription(): string
    {
        $description = 'The old sf_register plugin using switchableControllerActions has been split into 4 separate plugins. ';
        $description .= 'This update wizard migrates all existing plugin settings and changes the Plugin (list_type) ';
        $description .= 'to use the new plugins available.';
        return $description;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->checkIfWizardIsRequired();
    }

    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    public function checkIfWizardIsRequired(): bool
    {
        return count($this->getMigrationRecords()) > 0;
    }

    public function performMigration(): bool
    {
        $records = $this->getMigrationRecords();

        foreach ($records as $record) {
            $flexFormData = GeneralUtility::xml2array($record['pi_flexform']);
            $flexForm = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
            $targetListType = $this->getTargetListType(
                $record['list_type'],
                $flexForm['switchableControllerActions']
            );
            $allowedSettings = $this->getAllowedSettingsFromFlexForm($targetListType);

            // Remove flexform data which do not exist in flexform of new plugin
            foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
                foreach ($sheetData['lDEF'] as $settingName => $setting) {
                    if (!in_array($settingName, $allowedSettings, true)) {
                        unset($flexFormData['data'][$sheetKey]['lDEF'][$settingName]);
                    }
                }

                // Remove empty sheets
                if (!count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                    unset($flexFormData['data'][$sheetKey]);
                }
            }

            if (count($flexFormData['data']) > 0) {
                $newFlexform = $this->array2xml($flexFormData);
            } else {
                $newFlexform = '';
            }

            $this->updateContentElement($record['uid'], $targetListType, $newFlexform);
        }

        return true;
    }

    protected function getMigrationRecords(): array
    {
        $checkListTypes = array_unique(array_column(self::MIGRATION_SETTINGS, 'sourceListType'));

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'list_type', 'pi_flexform')
            ->from('tt_content')
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
        foreach (self::MIGRATION_SETTINGS as $setting) {
            if ($setting['sourceListType'] === $sourceListType &&
                $setting['switchableControllerActions'] === $switchableControllerActions
            ) {
                return $setting['targetListType'];
            }
        }

        return '';
    }

    protected function getAllowedSettingsFromFlexForm(string $listType): array
    {
        $flexFormFile = $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][$listType . ',list'];
        $flexFormContent = file_get_contents(GeneralUtility::getFileAbsFileName(substr(trim($flexFormFile), 5)));
        $flexFormData = GeneralUtility::xml2array($flexFormContent);

        // Iterate each sheet and extract all settings
        $settings = [];
        foreach ($flexFormData['sheets'] as $sheet) {
            foreach ($sheet['ROOT']['el'] as $setting => $tceForms) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    /**
     * Updates list_type and pi_flexform of the given content element UID
     *
     * @param int $uid
     * @param string $newListType
     * @param string $flexform
     */
    protected function updateContentElement(int $uid, string $newListType, string $flexform): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('list_type', $newListType)
            ->set('pi_flexform', $flexform)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    /**
     * Transforms the given array to FlexForm XML
     *
     * @param array $input
     * @return string
     */
    protected function array2xml(array $input = []): string
    {
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
        $spaceInd = 4;
        $output = GeneralUtility::array2xml($input, '', 0, 'T3FlexForms', $spaceInd, $options);
        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . LF . $output;
    }
}
