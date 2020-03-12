<?php


namespace CPSIT\AdmiralcloudConnector\Resource;

use CPSIT\AdmiralcloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralcloudConnector\Exception\InvalidAssetException;
use CPSIT\AdmiralcloudConnector\Traits\AdmiralcloudStorage;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;

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
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->getInformation()['type'] === self::TYPE_IMAGE;
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

    public function getThumbnail(): ?string
    {
        // TODO get thumbnail url from admiral cloud
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
     * @return array
     */
    public function getInformation(): array
    {
        if ($this->information === null) {
            try {
                // Do API call
                // TODO api call
                //$this->information = $this->getBynderService()->getMediaInfo($this->getIdentifier());
                $this->information = ['type' => self::TYPE_IMAGE];
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
        // TODO implement me

        switch ($property) {
            case 'name':
                return $this->identifier . '.jpg';
            case 'mimetype':
                return 'admiralcloud/image/jpg';
            case 'storage':
                return $this->getAdmiralCloudStorage()->getUid();
            case 'extension':
                return 'jpg';
            case 'size':
                return 100;
            case 'atime':
            case 'mtime':
            case 'ctime':
                return time();
            case 'identifier':
                return $this->identifier;
            case 'identifier_hash':
                return sha1($this->identifier);
            case 'folder_hash':
                return sha1('admiralcloud' . $this->getAdmiralCloudStorage()->getUid());

            // Metadata
            case 'title':
                return 'title';
            case 'description':
                return 'description';
            case 'width':
                return 300;
            case 'height':
                return 150;
            case 'copyright':
                return 'copyright';
            case 'keywords':
                return 'hello,world';
        }

        return '';

        // TODO implement me

        $information = $this->getInformation();
        switch ($property) {
            case 'size':
                return $information['fileSize'];
            case 'atime':
                return strtotime($information['dateModified']);
            case 'mtime':
                return strtotime($information['dateModified']);
            case 'ctime':
                return strtotime($information['dateCreated']);
            case 'name':
                return $information['fileName'] . '.' . $information['fileExtension'];
            case 'mimetype':
                return 'bynder/' . $information['type'];
            case 'identifier':
                return $information['id'];
            case 'extension':
                return $information['fileExtension'];
            case 'identifier_hash':
                return sha1($information['id']);
            case 'storage':
                return $this->getAdmiralCloudStorage()->getUid();
            case 'folder_hash':
                return sha1('bynder' . $this->getAdmiralCloudStorage()->getUid());

            // Metadata
            case 'title':
                return $information['name'];
            case 'description':
                return $information['description'];
            case 'width':
                return $information['width'];
            case 'height':
                return $information['height'];
            case 'copyright':
                return $information['copyright'];
            case 'keywords':
                return implode(', ', $information['tags'] ?? []);
            default:
                throw new InvalidPropertyException(sprintf('The information "%s" is not available.', $property), 1519130380);
        }
    }
}