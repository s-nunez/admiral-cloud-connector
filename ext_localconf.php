<?php

use CPSIT\AdmiralcloudConnector\Backend\InlineControlContainer;

defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1433198160] = [
    'nodeName' => 'inline',
    'priority' => 50,
    'class' => InlineControlContainer::class,
];

// Register the FAL driver for AdmiralCloud
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers'][\CPSIT\AdmiralcloudConnector\Resource\AdmiralcloudDriver::KEY] = [
'class' => \CPSIT\AdmiralcloudConnector\Resource\AdmiralcloudDriver::class,
'label' => 'Admiral Cloud',
// @todo: is currently needed to not break the backend. Needs to be fixed in TYPO3
'flexFormDS' => 'FILE:EXT:admiralcloud_connector/Configuration/FlexForms/AdmiralcloudDriverFlexForm.xml'
];

// Register slot to use AdmiralCloud API for processed file
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)
    ->connect(
        \TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
        'afterExtensionInstall',
        \CPSIT\AdmiralcloudConnector\Slot\InstallSlot::class,
        'createAdmiralCloudFileStorage'
    );

\TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance()
    ->registerRendererClass(\CPSIT\AdmiralcloudConnector\Resource\Rendering\AssetRenderer::class);

// Register the extractor to fetch metadata from AdmiralCloud
\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()
    ->registerExtractionService(\CPSIT\AdmiralcloudConnector\Resource\Index\Extractor::class);

// Override TYPO3 File class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\File::class] = [
    'className' => \CPSIT\AdmiralcloudConnector\Resource\File::class
];

// Override TYPO3 File class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\ProcessedFile::class] = [
    'className' => \CPSIT\AdmiralcloudConnector\Resource\ProcessedFile::class
];

// Override TYPO3 FileIndexRepository class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class] = [
    'className' => \CPSIT\AdmiralcloudConnector\Resource\Index\FileIndexRepository::class
];

// Override Fluid ImageViewHelper class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper::class] = [
    'className' => \CPSIT\AdmiralcloudConnector\ViewHelpers\ImageViewHelper::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
    'nodeName' => 'admiralCloudImageManipulation',
    'class' => \CPSIT\AdmiralcloudConnector\Form\Element\AdmiralCloudImageManipulationElement::class,
    'priority' => 50
];

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'actions-admiralcloud-browser',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:admiralcloud_connector/Resources/Public/Icons/actions-admiralcloud-browser.svg']
);
$iconRegistry->registerIcon(
    'permissions-admiralcloud-browser',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:admiralcloud_connector/Resources/Public/Icons/permissions-admiralcloud-browser.svg']
);
unset($iconRegistry);


