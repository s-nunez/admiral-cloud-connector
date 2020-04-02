<?php


namespace CPSIT\AdmiralcloudConnector\Resource;

use CPSIT\AdmiralcloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralcloudConnector\Exception\InvalidAssetException;
use CPSIT\AdmiralcloudConnector\Exception\InvalidPropertyException;
use CPSIT\AdmiralcloudConnector\Exception\InvalidThumbnailException;
use CPSIT\AdmiralcloudConnector\Resource\Index\FileIndexRepository;
use CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService;
use CPSIT\AdmiralcloudConnector\Traits\AdmiralcloudStorage;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Asset
 * @package CPSIT\AdmiralcloudConnector\Resource
 */
class Asset
{
    use AdmiralcloudStorage;

    /**
     * Available Types used by Admiralcloud
     */
    const TYPE_VIDEO = 'video';
    const TYPE_IMAGE = 'image';
    const TYPE_DOCUMENT = 'document';
    const TYPE_AUDIO = 'audio';

    /**
     * @var int
     */
    protected $identifier;

    /**
     * API Data
     * @var array
     */
    protected $information;

    /**
     * Asset constructor.
     * @param string $identifier
     * @param array $properties
     * @throws InvalidAssetException
     */
    public function __construct(string $identifier, array $information = [])
    {
        if (!static::validateIdentifier($identifier)) {
            throw new InvalidAssetException(
                'Invalid identifier given: ' . $identifier,
                1558014684521
            );
        }

        $this->identifier = $identifier;
        if ($information) {
            $this->information = $information;
        }
    }

    /**
     * Identifier patern should be bigger than 0
     * @param int $identifier
     * @return bool
     */
    public static function validateIdentifier(int $identifier): bool
    {
        return $identifier > 0;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @param int $storageUid
     * @return bool
     */
    public function isImage(int $storageUid = 0): bool
    {
        return $this->getInformation($storageUid)['type'] === self::TYPE_IMAGE;
    }

    /**
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this->getInformation()['type'] === self::TYPE_VIDEO;
    }

    /**
     * @return bool
     */
    public function isAudio(): bool
    {
        return $this->getInformation()['type'] === self::TYPE_AUDIO;
    }

    /**
     * @return bool
     */
    public function isDocument(): bool
    {
        return $this->getInformation()['type'] === self::TYPE_DOCUMENT;
    }

    public function getThumbnail(int $storageUid = 0): ?string
    {
        $fileData = $this->getFileIndexRepository()->findOneByStorageUidAndIdentifier(
            ($storageUid? $storageUid:$this->getAdmiralCloudStorage()->getUid()),
            $this->identifier
        );

        $file = GeneralUtility::makeInstance(File::class, $fileData, $this->getAdmiralCloudStorage($storageUid));

        return $this->getAdmiralcloudService()->getThumbnailUrl($file);
    }

    /**
     * @param string $width
     * @param string $height
     * @return string
     * @throws InvalidExtensionConfigurationException
     * @throws InvalidThumbnailException
     */
    public function getOnTheFlyPublicUrl($width, $height): string
    {
        // TODO implement me or remove me if it is not needed
    }

    /**
     * @param int $storageUid
     * @return array
     */
    public function getInformation(int $storageUid = 0): array
    {
        if ($this->information === null) {
            try {
                // Do API call
                $this->information = $this->getAdmiralcloudService()->getMediaInfo([$this->identifier], ($storageUid?:$this->getAdmiralCloudStorage()->getUid()))[$this->identifier];

            } catch (\Exception $e) {
                $this->information = [];
            }
        }
        return $this->information;
    }

    /**
     * Extracts information about a file from the filesystem
     *
     * @param array $propertiesToExtract array of properties which should be returned, if empty all default keys will be extracted
     * @return array
     */
    public function extractProperties($propertiesToExtract = []): array
    {
        // TODO implement me

        if (empty($propertiesToExtract)) {
            $propertiesToExtract = [
                'size',
                'atime',
                'mtime',
                'ctime',
                'mimetype',
                'name',
                'extension',
                'identifier',
                'identifier_hash',
                'storage',
                'folder_hash'
            ];
        }
        $fileInformation = [];
        foreach ($propertiesToExtract as $property) {
            $fileInformation[$property] = $this->getSpecificProperty($property);
        }
        return $fileInformation;
    }

    /**
     * Extracts a specific FileInformation from the FileSystem
     *
     * @param string $property
     * @return bool|int|string
     */
    public function getSpecificProperty($property)
    {
        $information = $this->getInformation();
        switch ($property) {
            case 'size':
                return $information['size'];
            case 'atime':
                return $information['atime'];
            case 'mtime':
                return $information['mtime'];
            case 'ctime':
                return $information['ctime'];
            case 'name':
                return $information['name'];
            case 'mimetype':
                return $information['mimetype'];
            case 'identifier':
                return $information['identifier'];
            case 'extension':
                return $information['extension'];
            case 'identifier_hash':
                return $information['identifier_hash'];
            case 'storage':
                return $information['storage'];
            case 'folder_hash':
                return $information['folder_hash'];

            // Metadata
            case 'title':
                return $information['title'];
            case 'description':
                return $information['description'];
            case 'width':
                return $information['width'];
            case 'height':
                return $information['height'];
            case 'copyright':
                return $information['copyright'];
            case 'keywords':
                return $information['keywords'];
            default:
                throw new InvalidPropertyException(sprintf('The information "%s" is not available.', $property), 1519130380);
        }
    }

    /**
     * Save a file to a temporary path and returns that path.
     *
     * @return string|null The temporary path
     * @throws InvalidThumbnailException
     */
    public function getLocalThumbnail(int $storageUid=0): ?string
    {
        $url = $this->getThumbnail($storageUid);
        if (!empty($url)) {
            $temporaryPath = $this->getTemporaryPathForFile($url);
            if (!is_file($temporaryPath)) {
                try {
                    $data = GeneralUtility::getUrl($url, 0, false);
                } catch (\Exception $e) {
                    throw new InvalidThumbnailException(
                        sprintf('Requested url "%s" couldn\'t be found', $url),
                        1558442606611,
                        $e
                    );
                }
                if (!empty($data)) {
                    $result = GeneralUtility::writeFile($temporaryPath, $data);
                    if ($result === false) {
                        throw new InvalidThumbnailException(
                            sprintf('Copying file "%s" to temporary path "%s" failed.', $this->getIdentifier(), $temporaryPath),
                            1558442609629
                        );
                    }
                }
            }

            // Return absolute path instead of relative when configured
            return $temporaryPath;
        }
        return $temporaryPath ?? null;
    }

    /**
     * Returns a temporary path for a given file, including the file extension.
     *
     * @param string $url
     * @return string
     */
    protected function getTemporaryPathForFile($url): string
    {
        $temporaryPath = PATH_site . 'typo3temp/assets/' . AdmiralcloudDriver::KEY . '/';
        if (!is_dir($temporaryPath)) {
            GeneralUtility::mkdir_deep($temporaryPath);
        }

        $info = $this->getInformation();

        return $temporaryPath . $info['name'];
    }

    /**
     * @return AdmiralcloudService
     */
    protected function getAdmiralcloudService()
    {
        return GeneralUtility::makeInstance(AdmiralcloudService::class);
    }

    /**
     * @return FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return GeneralUtility::makeInstance(FileIndexRepository::class);
    }
}
