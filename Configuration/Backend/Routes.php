<?php
/*
 * This source file is proprietary property of Beech.it
 * Date: 20-2-18
 * All code (c) Beech.it all rights reserved
 */

use CPSIT\AdmiralCloudConnector\Controller\Backend\BrowserController;

return [
    'admiral_cloud_browser_show' => [
        'path' => '/admiral_cloud/browser/show',
        'target' => BrowserController::class . '::showAction'
    ],
    'admiral_cloud_browser_upload' => [
        'path' => '/admiral_cloud/browser/upload',
        'target' => BrowserController::class . '::uploadAction'
    ],
    'admiral_cloud_browser_crop' => [
        'path' => '/admiral_cloud/browser/crop',
        'target' => BrowserController::class . '::cropAction'
    ],
    'admiral_cloud_browser_api' => [
        'path' => '/admiral_cloud/browser/api',
        'target' => BrowserController::class . '::apiAction'
    ],
    'admiral_cloud_browser_rte_link' => [
        'path' => '/admiral_cloud/browser/rte-link',
        'target' => BrowserController::class . '::rteLinkAction'
    ],
];
