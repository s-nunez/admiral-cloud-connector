<?php
call_user_func(function ($extension, $table) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'file_permissions',
        [
            'LLL:EXT:' . $extension . '/Resources/Private/Language/locallang_be.xlf:be_users.file_permissions.folder_add_via_admiral_cloud',
            'addFileViaAdmiralCloud',
            'permissions-admiral_cloud-browser'
        ],
        'addFile',
        'after'
    );
}, 'admiral_cloud_connector', 'be_users');


/**
 * Add extra fields to the be_users record
 */
$newBeUsersColumns = [
    'first_name' => [
        'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:be_users.first_name',
        'config' => [
            'type' => 'input',
            'size' => 15,
            'eval' => 'trim'
        ]
    ],
    'last_name' => [
        'label' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:be_users.last_name',
        'config' => [
            'type' => 'input',
            'size' => 15,
            'eval' => 'trim'
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $newBeUsersColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users',
    'first_name,last_name', '', 'after:realName');
