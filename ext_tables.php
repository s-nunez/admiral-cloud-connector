<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$emSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (TYPO3_MODE === 'BE') {
    $pathEventIcon = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/ac.svg';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'Admiralcloud',
        '',
        '',
        [
            'routeTarget' => '',
            'access' => 'group,user',
            'name' => 'events',
            'icon' => 'EXT:t3events/Resources/Public/Icons/event-calendar.svg',
            'labels' => 'LLL:EXT:admiralcloud_connector/Resources/Private/Language/locallang_mod_main.xlf',
        ]
    );


    /**
     * Register Backend Modules
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'CPSIT.' . $_EXTKEY,
        'Admiralcloud',
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
