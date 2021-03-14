<?php

namespace Evoweb\SfRegister\Services\Setup;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CheckFactory
{
    protected array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getCheckInstances(): array
    {
        $checks = [];

        foreach ($this->configuration['checks'] as $checkClassname) {
            $checks[] = GeneralUtility::makeInstance($checkClassname);
        }

        return $checks;
    }
}
