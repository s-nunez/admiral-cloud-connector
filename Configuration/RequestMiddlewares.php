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
];
