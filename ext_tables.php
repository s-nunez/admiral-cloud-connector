<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$emSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (TYPO3_MODE === 'BE') {
    // Register as a skin
    if (getenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD') == 1) {
        $GLOBALS['TBE_STYLES']['skins'][$_EXTKEY] = [
            'name' => $_EXTKEY,
            'stylesheetDirectories' => [
                'css' => 'EXT:admiral_cloud_connector/Resources/Public/Backend/Css/'
            ]
        ];
    }

    $pathEventIcon = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/ac.svg';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'AdmiralCloud',
        '',
        '',
        [
            'routeTarget' => '',
            'access' => 'group,user',
            'name' => 'events',
            'icon' => 'EXT:t3events/Resources/Public/Icons/event-calendar.svg',
            'labels' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_mod_main.xlf',
        ]
    );


    /**
     * Register Backend Modules
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'CPSIT.' . $_EXTKEY,
        'AdmiralCloud',
        'm1',
        '',
        [
            'Backend\Browser' => 'api',
        ],
        [
            'access' => 'user,group',
            'icon' => $pathEventIcon,
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_m1.xlf',
        ]
    );

}
