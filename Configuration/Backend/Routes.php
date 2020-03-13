<?php
/*
 * This source file is proprietary property of Beech.it
 * Date: 20-2-18
 * All code (c) Beech.it all rights reserved
 */

use CPSIT\AdmiralcloudConnector\Controller\Backend\BrowserController;

return [
    'admiralcloud_browser_show' => [
        'path' => '/admiralcloud/show',
        'target' => BrowserController::class . '::showAction'
    ],
    'admiralcloud_browser_api' => [
        'path' => '/admiralcloud/browser/api',
        'target' => BrowserController::class . '::apiAction'
    ],
];
