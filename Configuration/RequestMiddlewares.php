<?php
return [
    'backend' => [
        'cpsit/admiral-cloud-middleware' => [
            'target' => \CPSIT\AdmiralCloudConnector\Middleware\AdmiralCloudMiddleware::class,
            'after' => [
                'typo3/cms-backend/authentication',
            ],
        ],
    ],
    'frontend' => [
        'cpsit/admiralcloudconnector/readablelinkresolver' => [
            'target' => \CPSIT\AdmiralCloudConnector\Http\Middleware\ReadableLinkResolver::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
        ],
    ],
];
