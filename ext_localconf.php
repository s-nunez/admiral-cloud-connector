<?php

use CPSIT\AdmiralCloudConnector\Backend\InlineControlContainer;

defined('TYPO3_MODE') || die('Access denied.');

// Adding pageTS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:admiral_cloud_connector/Configuration/TSconfig/LinkHandler.ts">'
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1433198160] = [
    'nodeName' => 'inline',
    'priority' => 50,
    'class' => InlineControlContainer::class,
];

// Register the FAL driver for AdmiralCloud
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['registeredDrivers'][\CPSIT\AdmiralCloudConnector\Resource\AdmiralCloudDriver::KEY] = [
'class' => \CPSIT\AdmiralCloudConnector\Resource\AdmiralCloudDriver::class,
'label' => 'Admiral Cloud',
// @todo: is currently needed to not break the backend. Needs to be fixed in TYPO3
'flexFormDS' => 'FILE:EXT:admiral_cloud_connector/Configuration/FlexForms/AdmiralCloudDriverFlexForm.xml'
];

// Register slot to use AdmiralCloud API for processed file
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)
    ->connect(
        \TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
        'afterExtensionInstall',
        \CPSIT\AdmiralCloudConnector\Slot\InstallSlot::class,
        'createAdmiralCloudFileStorage'
    );

\TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance()
    ->registerRendererClass(\CPSIT\AdmiralCloudConnector\Resource\Rendering\AssetRenderer::class);

// Register the extractor to fetch metadata from AdmiralCloud
\TYPO3\CMS\Core\Resource\Index\ExtractorRegistry::getInstance()
    ->registerExtractionService(\CPSIT\AdmiralCloudConnector\Resource\Index\Extractor::class);

// Override TYPO3 File class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\File::class] = [
    'className' => \CPSIT\AdmiralCloudConnector\Resource\File::class
];

// Override TYPO3 File class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\ProcessedFile::class] = [
    'className' => \CPSIT\AdmiralCloudConnector\Resource\ProcessedFile::class
];

// Override TYPO3 FileIndexRepository class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class] = [
    'className' => \CPSIT\AdmiralCloudConnector\Resource\Index\FileIndexRepository::class
];

// Override Fluid ImageViewHelper class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper::class] = [
    'className' => \CPSIT\AdmiralCloudConnector\ViewHelpers\ImageViewHelper::class
];

// Override Fluid ImageViewHelper class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper::class] = [
    'className' => \CPSIT\AdmiralCloudConnector\ViewHelpers\Uri\ImageViewHelper::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
    'nodeName' => 'admiralCloudImageManipulation',
    'class' => \CPSIT\AdmiralCloudConnector\Form\Element\AdmiralCloudImageManipulationElement::class,
    'priority' => 50
];

// Add task to update metadata of AdmiralCloud files
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\CPSIT\AdmiralCloudConnector\Task\UpdateAdmiralCloudMetadataTask::class] = [
    'extension' => 'admiral_cloud_connector',
    'title' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.update_admiral_cloud_metadata.name',
    'description' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.update_admiral_cloud_metadata.description',
    'additionalFields' => \CPSIT\AdmiralCloudConnector\Task\UpdateAdmiralCloudMetadataAdditionalFieldProvider::class
];

// Add task to export sys_file with metadata for AdmiralCloud
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\CPSIT\AdmiralCloudConnector\Task\ExportSysFileWithMetadataTask::class] = [
    'extension' => 'admiral_cloud_connector',
    'title' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.name',
    'description' => 'LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:task.export_sys_file_with_metadata.description',
    'additionalFields' => \CPSIT\AdmiralCloudConnector\Task\ExportSysFileWithMetadataTaskAdditionalFieldProvider::class
];

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'actions-admiral_cloud-browser',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:admiral_cloud_connector/Resources/Public/Icons/actions-admiral_cloud-browser.svg']
);
$iconRegistry->registerIcon(
    'permissions-admiral_cloud-browser',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:admiral_cloud_connector/Resources/Public/Icons/permissions-admiral_cloud-browser.svg']
);
unset($iconRegistry);

/**
 * register cache for extension
 */
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector'])) {
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector'] = array();
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    $TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['admiral_cloud_connector']['backend'] = \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
}


