<?php

$additionalFields = [
    'tx_admiralcloudconnector_linkhash' => [
        'label' => 'LLL:EXT:admiralcloud_connector/Resources/Private/Language/locallang_be.xlf:sys_file.tx_admiralcloudconnector_linkhash',
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
