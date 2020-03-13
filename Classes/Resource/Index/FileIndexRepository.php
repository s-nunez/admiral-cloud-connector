<?php


namespace CPSIT\AdmiralcloudConnector\Resource\Index;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileIndexRepository
 * @package CPSIT\AdmiralcloudConnector\Resource\Index
 */
class FileIndexRepository extends \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
{
    protected $extendedFields = [
        'tx_admiralcloudconnector_linkhash'
    ];

    public function mergeFieldsWithExtendedFields(): void
    {
        $this->fields = array_merge($this->fields, $this->extendedFields);
    }

    /**
     * Returns an Instance of the Repository
     *
     * @return FileIndexRepository
     */
    public static function getInstance()
    {
        $instance = GeneralUtility::makeInstance(self::class);

        $instance->mergeFieldsWithExtendedFields();

        return $instance;
    }
}