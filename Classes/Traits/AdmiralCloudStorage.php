<?php

namespace CPSIT\AdmiralCloudConnector\Traits;

use CPSIT\AdmiralCloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralCloudConnector\Resource\AdmiralCloudDriver;
use CPSIT\AdmiralCloudConnector\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
            /** @var StorageRepository $storageRepository */
            $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
            $storageObjects = $storageRepository->findAll();
            foreach ($storageObjects as $fileStorage) {
                if ($fileStorage->getDriverType() === AdmiralCloudDriver::KEY) {
                    return $this->admiralCloudStorage = $fileStorage;
                }
            }
            throw new InvalidArgumentException('Missing Admiral Cloud file storage', 1559128872210);
        }
        return $this->admiralCloudStorage;
    }

    /**
     * Gets the Indexer.
     *
     * @param ResourceStorage $storage
     * @return Indexer
     */
    protected function getIndexer(ResourceStorage $storage): Indexer
    {
        return GeneralUtility::makeInstance(Indexer::class, $storage);
    }

    /**
     * @return FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return FileIndexRepository::getInstance();
    }
}
