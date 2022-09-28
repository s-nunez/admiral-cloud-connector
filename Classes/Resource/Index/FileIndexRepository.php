<?php


namespace CPSIT\AdmiralCloudConnector\Resource\Index;

use Psr\EventDispatcher\EventDispatcherInterface;
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

    public function __construct(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->fields = array_merge($this->fields, $this->extendedFields);
    }
}
