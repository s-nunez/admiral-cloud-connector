<?php


namespace CPSIT\AdmiralCloudConnector\Resource\Index;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileIndexRepository
 * @package CPSIT\AdmiralCloudConnector\Resource\Index
 */
class FileIndexRepository extends \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
{
    protected $extendedFields = [
        'tx_admiralcloudconnector_linkhash'
    ];

    public function __construct()
    {
        $this->fields = array_merge($this->fields, $this->extendedFields);
    }
}
