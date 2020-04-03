<?php

$additionalFields = [
    'tx_admiralcloudconnector_linkhash' => [
        'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:sys_file.tx_admiral_cloudconnector_linkhash',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 255,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file',
    $additionalFields,
    1
);
