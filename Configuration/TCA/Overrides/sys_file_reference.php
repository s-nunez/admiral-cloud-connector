<?php

$additionalFields = [
    'tx_admiralcloudconnector_crop' => [
        'exclude' => true,
        'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:sys_file_reference.tx_admiralcloudconnector_crop',
        'config' => [
            'type' => 'input',
            'renderType' => 'admiralCloudImageManipulation'
        ],
        'displayCond' => 'FIELD:tx_admiralcloudconnector_crop:REQ:true',
    ],
];

// Show TYPO3 crop field only if AdmiralCloud crop is empty
// AdmiralCloud files have always crop information
$GLOBALS['TCA']['sys_file_reference']['columns']['crop']['displayCond'] = 'FIELD:tx_admiralcloudconnector_crop:REQ:false';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_reference',
    $additionalFields,
    1
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_reference',
    'imageoverlayPalette',
    '--linebreak--,tx_admiralcloudconnector_crop'
);
