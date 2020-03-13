<?php

namespace CPSIT\AdmiralcloudConnector\Service;
use CPSIT\AdmiralcloudConnector\Api\AdmiralcloudApi;
use CPSIT\AdmiralcloudConnector\Api\AdmiralcloudApiFactory;
use CPSIT\AdmiralcloudConnector\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
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
class AdmiralcloudService implements SingletonInterface
{
    /**
     * @var AdmiralcloudApi
     */
    protected $admiralcloudApi;

    public function getAdmiralcloudAuthCode($settings): string
    {
        try {
            return AdmiralcloudApiFactory::auth($settings);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('BynderApi cannot be created', 1559128418168, $e);
        }
    }

    public function getAdmiralcloudApi($settings): AdmiralcloudApi
    {
        try {
            return AdmiralcloudApiFactory::create($settings);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('BynderApi cannot be created', 1559128418168, $e);
        }
    }

    /**
     * @param array $identifiers
     * @return string
     */
    public function getMediaInfo(array $identifiers): array
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
        $fileInfo = $this->getAdmiralcloudApi($settings)->getData();
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
        $metaData = json_decode($this->getAdmiralcloudApi($settings)->getData());
        DebuggerUtility::var_dump($metaData);die();
        return $metaData;
    }
}
