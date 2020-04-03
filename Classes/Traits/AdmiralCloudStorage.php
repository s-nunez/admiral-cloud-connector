<?php

namespace CPSIT\AdmiralCloudConnector\Traits;

use CPSIT\AdmiralCloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralCloudConnector\Resource\AdmiralCloudDriver;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;

/**
 * Trait AdmiralCloudStorage
 * @package CPSIT\AdmiralCloudConnector\Traits
 */
trait AdmiralCloudStorage
{
    /**
     * @var ResourceStorageInterface
     */
    protected $admiralCloudStorage;

    /**
     * @return ResourceStorageInterface
     */
    protected function getAdmiralCloudStorage(int $storageUid = 0): ResourceStorage
    {
        if($storageUid > 0){
            $this->admiralCloudStorage = ResourceFactory::getInstance()->getStorageObject($storageUid);
        }
        if ($this->admiralCloudStorage === null) {
            $backendUserAuthentication = $GLOBALS['BE_USER'];
            foreach ($backendUserAuthentication->getFileStorages() as $fileStorage) {
                if ($fileStorage->getDriverType() === AdmiralCloudDriver::KEY) {
                    return $this->admiralCloudStorage = $fileStorage;
                }
            }
            throw new InvalidArgumentException('Missing Admiral Cloud file storage', 1559128872210);
        }
        return $this->admiralCloudStorage;
    }
}
