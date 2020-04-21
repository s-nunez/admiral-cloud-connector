<?php

namespace CPSIT\AdmiralCloudConnector\Service;

use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApi;
use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApiFactory;
use CPSIT\AdmiralCloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralCloudConnector\Exception\InvalidFileConfigurationException;
use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use CPSIT\AdmiralCloudConnector\Utility\ImageUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2020
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class AdmiralCloudService implements SingletonInterface
{
    use AdmiralCloudStorage;

    /**
     * @var AdmiralCloudApi
     */
    protected $admiralCloudApi;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AdmiralCloudService constructor.
     */
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * @param array $settings
     * @return string
     */
    public function getAdmiralCloudAuthCode(array $settings): string
    {
        try {
            return AdmiralCloudApiFactory::auth($settings);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('AdmiralCloud Auth Code cannot be created', 1559128418168, $e);
        }
    }

    /**
     * @param array $settings
     * @return AdmiralCloudApi
     */
    public function callAdmiralCloudApi(array $settings): AdmiralCloudApi
    {
        try {
            $this->admiralCloudApi = AdmiralCloudApiFactory::create($settings);
            return $this->admiralCloudApi;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('AdmiralCloud API cannot be created', 1559128418168, $e);
        }
    }

    /**
     * @param array $identifiers
     * @return string
     */
    public function getMetaData(array $identifiers): array
    {
        $settings = [
            'route' => 'metadata/findBatch',
            'controller' => 'metadata',
            'action' => 'findbatch',
            'payload' => [
                'ids' => array_map(
                    function ($item) {
                        return (int)$item;
                    },
                    $identifiers
                ),
                'title' => [
                    'container_name',
                    'container_description',
                    'meta_copyright',
                    'meta_altTag',
                ],
            ]
        ];

        $fileInfoData = $this->callAdmiralCloudApi($settings)->getData();

        if (!$fileInfoData) {
            $this->logger->error('Empty data received after calling getMetaData of identifiers: '
                . implode(',', $identifiers));

            return [];
        }

        $fileInfo = json_decode($fileInfoData);

        if (!$fileInfo) {
            $this->logger->error(sprintf(
                'Error decoding JSON by getMetaData of identifiers [%s]. Json error code: %d. Json message: %s. Json: %s',
                implode(',', $identifiers),
                json_last_error(),
                json_last_error_msg(),
                $fileInfoData
            ));

            return [];
        }

        $metadata = [];
        foreach ($fileInfo as $file) {
            foreach ($settings['payload']['title'] as $index => $title) {
                $metadata[$file->mediaContainerId][$title] = '';
                if (strtolower($file->title) === strtolower($title)) {
                    $metadata[$file->mediaContainerId][$title] = $file->content;
                    unset($settings['payload']['title'][$index]);
                    break;
                }
            }
        }

        return $metadata;
    }

    /**
     * @param array $identifiers
     * @param int $admiralCloudStorageUid
     * @return array
     * @throws \Exception
     */
    public function getMediaInfo(array $identifiers, int $admiralCloudStorageUid = 0): array
    {
        if (!$admiralCloudStorageUid) {
            $admiralCloudStorageUid = $this->getAdmiralCloudStorage()->getUid();
        }

        $settings = [
            'route' => 'media/findBatch',
            'controller' => 'media',
            'action' => 'findbatch',
            'payload' => [
                'ids' => array_map(
                    function ($item) {
                        return (int)$item;
                    },
                    $identifiers
                )
            ]
        ];

        $fileMetaData = $this->getMetaData($identifiers);

        $fileInfoData = $this->callAdmiralCloudApi($settings)->getData();

        if (!$fileInfoData) {
            $this->logger->error('Empty data received after calling getMediaInfo of identifiers: '
                . implode(',', $identifiers));

            return [];
        }

        $fileInfo = json_decode($fileInfoData);

        if (!$fileInfo) {
            $this->logger->error(sprintf(
                'Error decoding JSON by getMediaInfo of identifiers [%s]. Json error code: %d. Json message: %s. Json: %s',
                implode(',', $identifiers),
                json_last_error(),
                json_last_error_msg(),
                $fileInfoData
            ));

            return [];
        }

        $mediaInfo = [];
        foreach ($fileInfo as $file) {
            $mediaInfo[$file->mediaContainerId] = [
                'type' => $file->type,
                'name' => $file->fileName . '_' . $file->mediaContainerId . '.' . $file->fileExtension,
                'mimetype' => 'admiralCloud/' . $file->type . '/' . $file->fileExtension,
                'storage' => $admiralCloudStorageUid,
                'extension' => $file->fileExtension,
                'size' => $file->fileSize,
                'atime' => (new \DateTime($file->updatedAt))->getTimestamp(),
                'mtime' => (new \DateTime($file->updatedAt))->getTimestamp(),
                'ctime' => (new \DateTime($file->createdAt))->getTimestamp(),
                'identifier' => $file->mediaContainerId,
                'identifier_hash' => sha1($file->mediaContainerId),
                'folder_hash' => sha1('AdmiralCloud' . $admiralCloudStorageUid),
                'alternative' => $fileMetaData[$file->mediaContainerId]['meta_altTag'] ?? '',
                'title' => $fileMetaData[$file->mediaContainerId]['container_name'] ?? '',
                'description' => $fileMetaData[$file->mediaContainerId]['container_description'] ?? '',
                'width' => $file->width,
                'height' => $file->height,
                'copyright' => $fileMetaData[$file->mediaContainerId]['meta_copyright'] ?? '',
                'keywords' => '',
            ];
        }

        return $mediaInfo;
    }

    /**
     * @param string $search
     * @return string
     */
    public function getSearch(string $search): array
    {
        $settings = [
            'route' => 'search',
            'controller' => 'search',
            'action' => 'search',
            'payload' => [
                'searchTerm' => $search
            ]
        ];
        return json_decode($this->callAdmiralCloudApi($settings)->getData()) ?? [];
    }

    /**
     * Get public url for AdmiralCloud video
     *
     * @param FileInterface $file
     * @return string
     */
    public function getVideoPublicUrl(FileInterface $file): string
    {
        return $this->getDirectPublicUrlForFile($file);
    }

    /**
     * Get public url for AdmiralCloud audio
     *
     * @param FileInterface $file
     * @return string
     */
    public function getAudioPublicUrl(FileInterface $file): string
    {
        return $this->getDirectPublicUrlForFile($file);
    }

    /**
     * Get public url for AdmiralCloud document
     *
     * @param FileInterface $file
     * @return string
     */
    public function getDocumentPublicUrl(FileInterface $file): string
    {
        return $this->getDirectPublicUrlForFile($file);
    }

    /**
     * Get public url for admiral cloud image
     *
     * @param FileInterface $file
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getImagePublicUrl(FileInterface $file, int $width = 0, int $height = 0): string
    {
        if ($file instanceof FileReference) {
            // Save crop information from FileReference and set it in the File object
            $crop = $file->getProperty('tx_admiralcloudconnector_crop');
            $file = $file->getOriginalFile();
            $file->setTxAdmiralCloudConnectorCrop($crop);
        }

        // Get width and height with the correct ratio
        $dimensions = ImageUtility::calculateDimensions(
            $file,
            $width,
            $height,
            (!$width) ? ConfigurationUtility::getDefaultImageWidth() : null
        );

        // Get image public url
        if ($file->getTxAdmiralCloudConnectorCrop()) {
            // With crop information
            $link = ConfigurationUtility::getSmartcropUrl() .'v3/deliverEmbed/'
                . $file->getTxAdmiralCloudConnectorLinkhash()
                . '/image/cropperjsfocus/'
                . $dimensions->width
                . '/'
                . $dimensions->height
                . '/'
                . $file->getTxAdmiralCloudConnectorCropUrlPath()
                . '?poc=true' . (!ConfigurationUtility::isProduction()?'&env=dev':'');
        } else {
            // Without crop information
            $link = ConfigurationUtility::getSmartcropUrl() . 'v3/deliverEmbed/'
                . $file->getTxAdmiralCloudConnectorLinkhash()
                . '/image/autocrop/'
                . $dimensions->width
                . '/'
                . $dimensions->height
                . '/0.8?poc=true';
        }

        return $link;
    }

    /**
     * Get public url for file thumbnail
     *
     * @param FileInterface $file
     * @return string
     */
    public function getThumbnailUrl(FileInterface $file): string
    {
        if ($file instanceof FileReference) {
            $file = $file->getOriginalFile();
        }

        return ConfigurationUtility::getThumbnailUrl() . '/v5/deliverEmbed/'
            . $file->getTxAdmiralCloudConnectorLinkhash()
            . '/image/144';
    }

    /**
     * @param array $mediaContainer
     * @return string
     * @throws InvalidFileConfigurationException
     */
    public function getLinkHashFromMediaContainer(array $mediaContainer): string
    {
        $links = $mediaContainer['links'] ?? [];

        $linkHash = '';

        // Flag Id for given media container type
        $flagId = ConfigurationUtility::getFlagPlayerConfigId();

        // Player configuration id for given media container type
        switch ($mediaContainer['type']) {
            case 'image':
                $playerConfigurationId = ConfigurationUtility::getImagePlayerConfigId();
                break;
            case 'video':
                $playerConfigurationId = ConfigurationUtility::getVideoPlayerConfigId();
                break;
            case 'audio':
                $playerConfigurationId = ConfigurationUtility::getAudioPlayerConfigId();
                break;
            case 'document':
                $playerConfigurationId = ConfigurationUtility::getDocumentPlayerConfigId();
                break;
            default:
                throw new InvalidFileConfigurationException(
                    'Any valid type was found for file in mediaContainer. Given type: ' . $mediaContainer['type'],
                    111222444580
                );
        }

        // Find link with flag id and player configuration id for given media container
        foreach ($links as $link) {
            if (isset($link['playerConfigurationId']) && isset($link['flag'])
                && $link['playerConfigurationId'] == $playerConfigurationId && $link['flag'] == $flagId) {
                $linkHash = $link['link'];
                break;
            }
        }

        // If there isn't link, it is not possible to obtain the public url
        // Link is required for AdmiralCloud field
        if (!$linkHash) {
            throw new InvalidFileConfigurationException(
                'Any valid hash was found for file in mediaContainer given configuration: ' . json_encode($mediaContainer),
                111222444578
            );
        }

        return $linkHash;
    }

    /**
     * Get direct public url for given hash
     *
     * @param string $hash
     * @return string
     */
    public function getDirectPublicUrlForHash(string $hash): string
    {
        return ConfigurationUtility::getDirectFileUrl() . $hash;
    }

    /**
     * Get direct public url for given file
     *
     * @param FileInterface $file
     * @return string
     */
    protected function getDirectPublicUrlForFile(FileInterface $file): string
    {
        return ConfigurationUtility::getDirectFileUrl() . $file->getTxAdmiralCloudConnectorLinkhash();
    }
}
