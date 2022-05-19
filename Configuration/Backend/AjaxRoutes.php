<?php
/*
 * This source file is proprietary property of Beech.it
 * Date: 20-2-18
 * All code (c) Beech.it all rights reserved
 */

use CPSIT\AdmiralCloudConnector\Controller\Backend\BrowserController;
use CPSIT\AdmiralCloudConnector\Controller\Backend\ToolbarController;

return [
    'admiral_cloud_browser_auth' => [
        'path' => '/admiral_cloud/browser/auth',
        'target' => BrowserController::class . '::authAction'
    ],
    'admiral_cloud_browser_get_files' => [
        'path' => '/admiral_cloud/browser/getfiles',
        'target' => BrowserController::class . '::getFilesAction'
    ],
    'admiral_cloud_browser_crop_file' => [
        'path' => '/admiral_cloud/browser/cropfile',
        'target' => BrowserController::class . '::cropFileAction'
    ],
    'admiral_cloud_browser_get_media_public_url' => [
        'path' => '/admiral_cloud/browser/getmediapublicurl',
        'target' => BrowserController::class . '::getMediaPublicUrlAction'
    ],
    'admiral_cloud_toolbar_update_changed_metadata' => [
        'path' => '/admiral_cloud/toolvar/updateChangedMetadata',
        'target' => ToolbarController::class . '::updateChangedMetadataAction'
    ],
];
