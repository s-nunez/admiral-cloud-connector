<?php

namespace CPSIT\AdmiralcloudConnector\Traits;

use CPSIT\AdmiralcloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralcloudConnector\Resource\AdmiralcloudDriver;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;

/**
 * Trait AdmiralcloudStorage
 * @package CPSIT\AdmiralcloudConnector\Traits
 */
trait AdmiralcloudStorage
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
                if ($fileStorage->getDriverType() === AdmiralcloudDriver::KEY) {
                    return $this->admiralCloudStorage = $fileStorage;
                }
            }
            throw new InvalidArgumentException('Missing Admiral Cloud file storage', 1559128872210);
        }
        return $this->admiralCloudStorage;
    }
}
