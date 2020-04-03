<?php
/*
 * This source file is proprietary property of Beech.it
 * Date: 20-2-18
 * All code (c) Beech.it all rights reserved
 */

use CPSIT\AdmiralCloudConnector\Controller\Backend\BrowserController;

return [
    'admiral_cloud_browser_auth' => [
        'path' => '/admiral_cloud/browser/auth',
        'target' => BrowserController::class . '::authAction'
    ],
    'admiral_cloud_browser_get_files' => [
        'path' => '/admiral_cloud/browser/getfiles',
        'target' => BrowserController::class . '::getFilesAction'
    ],
];
