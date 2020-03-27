<?php


namespace CPSIT\AdmiralcloudConnector\Resource;

use CPSIT\AdmiralcloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralcloudConnector\Exception\NotImplementedException;
use CPSIT\AdmiralcloudConnector\Traits\AssetFactory;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;

/**
 * Class AdmiralcloudDriver
 * @package CPSIT\AdmiralcloudConnector\Resource
 */
class AdmiralcloudDriver implements DriverInterface
{
    use AssetFactory;

    public const KEY = 'admiralCloud';

    /**
     * @var string
     */
    protected $rootFolder = '';

    /**
     * The capabilities of this driver. See Storage::CAPABILITY_* constants for possible values.
     *
     * @var int
     */
    protected $capabilities = 0;

    /**
     * The storage uid the driver was instantiated for
     *
     * @var int
     */
    protected $storageUid;

    /**
     * The configuration of this driver
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @inheritDoc
     */
    public function processConfiguration()
    {
    }

    /**
     * @inheritDoc
     */
    public function setStorageUid($storageUid)
    {
        $this->storageUid = $storageUid;
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->capabilities =
            ResourceStorage::CAPABILITY_BROWSABLE
            | ResourceStorage::CAPABILITY_PUBLIC
            | ResourceStorage::CAPABILITY_WRITABLE;
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities(): int
    {
        return $this->capabilities;
    }

    /**
     * @inheritDoc
     */
    public function mergeConfigurationCapabilities($capabilities): int
    {
        $this->capabilities &= $capabilities;
        return $this->capabilities;
    }

    /**
     * @inheritDoc
     */
    public function hasCapability($capability)
    {
        return (bool)($this->capabilities & $capability === (int)$capability);
    }

    /**
     * @inheritDoc
     */
    public function isCaseSensitiveFileSystem(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function sanitizeFileName($fileName, $charset = ''): string
    {
        // Admiral cloud allows all
        return $fileName;
    }

    /**
     * @inheritDoc
     */
    public function hashIdentifier($identifier): string
    {
        return $this->hash($identifier, 'sha1');
    }

    /**
     * @inheritDoc
     */
    public function getRootLevelFolder(): string
    {
        return $this->rootFolder;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultFolder(): string
    {
        return $this->rootFolder;
    }

    /**
     * @inheritDoc
     */
    public function getParentFolderIdentifierOfIdentifier($fileIdentifier): string
    {
        return $this->rootFolder;
    }

    /**
     * @inheritDoc
     */
    public function getPublicUrl($identifier): string
    {
        return $this->getAsset($identifier)->getThumbnail($this->storageUid);
    }

    /**
     * @inheritDoc
     */
    public function createFolder($newFolderName, $parentFolderIdentifier = '', $recursive = false)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045381);
    }

    /**
     * @inheritDoc
     */
    public function renameFolder($folderIdentifier, $newName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045382);
    }

    /**
     * @inheritDoc
     */
    public function deleteFolder($folderIdentifier, $deleteRecursively = false)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045383);
    }

    /**
     * @inheritDoc
     */
    public function fileExists($fileIdentifier)
    {
        // We just assume that the processed file exists as this is just a CDN link
        return !empty($fileIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function folderExists($folderIdentifier): bool
    {
        // We only know the root folder
        return $folderIdentifier === $this->rootFolder;
    }

    /**
     * @inheritDoc
     */
    public function isFolderEmpty($folderIdentifier): bool
    {
        return !$this->folderExists($folderIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function addFile($localFilePath, $targetFolderIdentifier, $newFileName = '', $removeOriginal = true)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045386);
    }

    /**
     * @inheritDoc
     */
    public function createFile($fileName, $parentFolderIdentifier)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045387);
    }

    /**
     * @inheritDoc
     */
    public function copyFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $fileName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045388);
    }

    /**
     * @inheritDoc
     */
    public function renameFile($fileIdentifier, $newName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045389);
    }

    /**
     * @inheritDoc
     */
    public function replaceFile($fileIdentifier, $localFilePath)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045390);
    }

    /**
     * @inheritDoc
     */
    public function deleteFile($fileIdentifier)
    {
        // Deleting processed files isn't needed as this is just a link to a file in the CDN
        // to prevent false errors for the user we just tell the API that deleting was successful
        if ($this->isProcessedFile($fileIdentifier)) {
            return true;
        }

        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045448);
    }

    /**
     * @inheritDoc
     */
    public function hash($fileIdentifier, $hashAlgorithm)
    {
        switch ($hashAlgorithm) {
            case 'sha1':
                return sha1($fileIdentifier);
            case 'md5':
                return md5($fileIdentifier);
            default:
                throw new InvalidArgumentException('Hash algorithm ' . $hashAlgorithm . ' is not implemented.', 1519131572);
        }
    }

    /**
     * @inheritDoc
     */
    public function moveFileWithinStorage($fileIdentifier, $targetFolderIdentifier, $newFileName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045392);
    }

    /**
     * @inheritDoc
     */
    public function moveFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045393);
    }

    /**
     * @inheritDoc
     */
    public function copyFolderWithinStorage($sourceFolderIdentifier, $targetFolderIdentifier, $newFolderName)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045394);
    }

    /**
     * @inheritDoc
     */
    public function getFileContents($fileIdentifier)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1530716557);
    }

    /**
     * @inheritDoc
     */
    public function setFileContents($fileIdentifier, $contents)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1519045395);
    }

    /**
     * @inheritDoc
     */
    public function fileExistsInFolder($fileName, $folderIdentifier)
    {
        return !empty($fileName) && ($this->rootFolder === $folderIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function folderExistsInFolder($folderName, $folderIdentifier)
    {
        // Currently we don't know the concept of folders within Admiral cloud and for now always return false
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFileForLocalProcessing($fileIdentifier, $writable = true)
    {
        return $this->getAsset($fileIdentifier)->getLocalThumbnail($this->storageUid);
    }

    /**
     * @inheritDoc
     */
    public function getPermissions($identifier)
    {
        return [
            'r' => $identifier === $this->rootFolder || $this->fileExists($identifier),
            'w' => false
        ];
    }

    /**
     * @inheritDoc
     */
    public function dumpFileContents($identifier)
    {
        throw new NotImplementedException(sprintf('Method %s::%s() is not implemented', __CLASS__, __METHOD__), 1530716441);
    }

    /**
     * @inheritDoc
     */
    public function isWithin($folderIdentifier, $identifier)
    {
        return ($folderIdentifier === $this->rootFolder);
    }

    /**
     * @inheritDoc
     */
    public function getFileInfoByIdentifier($fileIdentifier, array $propertiesToExtract = []): array
    {
        return $this->getAsset($fileIdentifier)->extractProperties($propertiesToExtract);
    }

    /**
     * @inheritDoc
     */
    public function getFolderInfoByIdentifier($folderIdentifier): array
    {
        return [
            'identifier' => $folderIdentifier,
            'name' => 'Admiralcloud',
            'mtime' => 0,
            'ctime' => 0,
            'storage' => $this->storageUid
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFileInFolder($fileName, $folderIdentifier): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getFilesInFolder(
        $folderIdentifier,
        $start = 0,
        $numberOfItems = 0,
        $recursive = false,
        array $filenameFilterCallbacks = [],
        $sort = '',
        $sortRev = false
    ) {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getFolderInFolder($folderName, $folderIdentifier): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getFoldersInFolder(
        $folderIdentifier,
        $start = 0,
        $numberOfItems = 0,
        $recursive = false,
        array $folderNameFilterCallbacks = [],
        $sort = '',
        $sortRev = false
    ) {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function countFilesInFolder($folderIdentifier, $recursive = false, array $filenameFilterCallbacks = []): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function countFoldersInFolder($folderIdentifier, $recursive = false, array $folderNameFilterCallbacks = []): int
    {
        return 0;
    }

    /**
     * @param string $fileIdentifier
     * @return bool
     */
    protected function isProcessedFile(string $fileIdentifier): bool
    {
        return (bool)preg_match('/^processed_([0-9A-Z\-]{35})_([a-z]+)/', $fileIdentifier);
    }
}
