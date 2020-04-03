<?php

namespace CPSIT\AdmiralCloudConnector\Service;

use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApi;
use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApiFactory;
use CPSIT\AdmiralCloudConnector\Exception\InvalidArgumentException;
use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\SingletonInterface;
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
    /**
     * @var AdmiralCloudApi
     */
    protected $admiralCloudApi;

    public function getAdmiralCloudAuthCode($settings): string
    {
        try {
            return AdmiralCloudApiFactory::auth($settings);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('AdmiralCloud Auth Code cannot be created', 1559128418168, $e);
        }
    }

    public function getAdmiralCloudApi($settings): AdmiralCloudApi
    {
        if ($this->admiralCloudApi) {
            return $this->admiralCloudApi;
        }

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
                'ids' => $identifiers,
                'title' => [
                    'container_name',
                    'container_description',
                    'metadata_copyright'
                ],
                'language' => 'de'
            ]
        ];
        $fileInfo = $this->getAdmiralCloudApi($settings)->getData();
        // TODO if error --> log it
        $metadata = [];
        foreach (json_decode($fileInfo) as $file){
            foreach ($settings['payload']['title'] as $title){
                $metadata[$file->mediaContainerId][$title] = '';
                if($file->title == $title){
                    $metadata[$file->mediaContainerId][$title] = $file->content;
                }
            }
        }

        return $metadata;
    }

    /**
     * @param array $identifiers
     * @return string
     */
    public function getMediaInfo(array $identifiers, int $admiralCloudStorageUid = 3): array
    {
        // TODO REMOVE this magic number
        $settings = [
            'route' => 'media/findBatch',
            'controller' => 'media',
            'action' => 'findbatch',
            'payload' => [
                'ids' => $identifiers
            ]
        ];
        $fileInfo = $this->getAdmiralCloudApi($settings)->getData();
        $fileMetaData = $this->getMetaData($identifiers);

        $mediaInfo = [];
        foreach (json_decode($fileInfo) as $file){
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
                'title' => $fileMetaData[$file->mediaContainerId]['container_name'],
                'description' => $fileMetaData[$file->mediaContainerId]['container_description'],
                'width' => $file->width,
                'height' => $file->height,
                'copyright' => $fileMetaData[$file->mediaContainerId]['metadata_copyright'],
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
        $metaData = json_decode($this->getAdmiralCloudApi($settings)->getData());
        DebuggerUtility::var_dump($metaData);
        die();
        return $metaData;
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
        // TODO implement me
        // TODO width, height
        if ($file instanceof FileReference) {
            // Save crop information from FileReference and set it in the File object
            $crop = $file->getProperty('tx_admiralcloudconnector_crop');
            $file = $file->getOriginalFile();
            $file->setTxAdmiralCloudConnectorCrop($crop);
        }

        $width = $width ?: 800;
        $height = $height ?: 600;

        if ($file->getTxAdmiralCloudConnectorCrop()) {
            $link = 'https://smartcropdev.admiralcloud.com/v3/deliverEmbed/'
                . $file->getTxAdmiralCloudConnectorLinkhash()
                . '/image/cropperjsfocus/'
                . $width
                . '/'
                . $height
                . '/'
                . $file->getTxAdmiralCloudConnectorCropUrlPath()
                . '?poc=true&env=dev';
        } else {
            $link = 'https://images.admiralcloud.com/v3/deliverEmbed/'
                . $file->getTxAdmiralCloudConnectorLinkhash()
                . '/image/autocrop/'
                . $width
                . '/'
                . $height
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
        if($file instanceof FileReference){
            $file = $file->getOriginalFile();
        }

        return 'https://imagesdev.admiralcloud.com/v5/deliverEmbed/'
            . $file->getTxAdmiralCloudConnectorLinkhash()
            . '/image/144';
    }
}
