<?php
call_user_func(function ($extension, $table) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'file_permissions',
        [
            'LLL:EXT:' . $extension . '/Resources/Private/Language/locallang_be.xlf:be_groups.file_permissions.folder_add_via_admiral_cloud',
            'addFileViaAdmiralCloud',
            'permissions-admiral_cloud-browser'
        ],
        'addFile',
        'after'
    );
}, 'admiral_cloud', 'be_groups');
