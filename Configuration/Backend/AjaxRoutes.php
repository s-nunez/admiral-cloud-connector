<?php
/*
 * This source file is proprietary property of Beech.it
 * Date: 20-2-18
 * All code (c) Beech.it all rights reserved
 */

use CPSIT\AdmiralcloudConnector\Controller\Backend\BrowserController;

return [
    'admiralcloud_browser_auth' => [
        'path' => '/admiralcloud/browser/auth',
        'target' => BrowserController::class . '::authAction'
    ],
    'admiralcloud_browser_get_files' => [
        'path' => '/admiralcloud/browser/getfiles',
        'target' => BrowserController::class . '::getFilesAction'
    ],
];
