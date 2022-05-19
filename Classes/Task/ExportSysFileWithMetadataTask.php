<?php

namespace CPSIT\AdmiralCloudConnector\Task;

use CPSIT\AdmiralCloudConnector\Exception\InvalidPropertyException;
use CPSIT\AdmiralCloudConnector\Service\ExportSysFileMetadataService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class ExportSysFileWithMetadataTask
 * @package CPSIT\AdmiralCloudConnector\Task
 */
class ExportSysFileWithMetadataTask extends AbstractTask
{
    /**
     * @var string
     */
    public $exportFilePath;

    /**
     * @var string
     */
    public $securityGroupMapping;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->getExportSysFileMetadataService()->generateFileWithSysFileMetadata(
            $this->exportFilePath,
            $this->prepareSecurityGroupMappingArray()
        );

        return true;
    }

    /**
     * Return array with mapping between path and security group
     *
     * @return array
     */
    protected function prepareSecurityGroupMappingArray(): array
    {
        if (!$this->securityGroupMapping) {
            throw new InvalidPropertyException('The security group mapping shouldn\'t be empty');
        }

        $scLines = explode("\r\n", $this->securityGroupMapping);

        $result = [];

        foreach ($scLines as $scLine) {
            [$path, $securityGroup] = explode(';', $scLine);

            $result[trim($path)] = trim($securityGroup);
        }

        return $result;
    }

    /**
     * @return ExportSysFileMetadataService
     */
    protected function getExportSysFileMetadataService(): ExportSysFileMetadataService
    {
        return GeneralUtility::makeInstance(ExportSysFileMetadataService::class);
    }
}