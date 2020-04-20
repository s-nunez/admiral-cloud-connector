<?php

namespace CPSIT\AdmiralCloudConnector\Backend;

/*
 * This source file is proprietary property of Beech.it
 * Date: 19-2-18
 * All code (c) Beech.it all rights reserved
 */



use CPSIT\AdmiralCloudConnector\Resource\AdmiralCloudDriver;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InlineControlContainer
 *
 * Override core InlineControlContainer to inject AdmiralCloud button
 */
class InlineControlContainer extends \TYPO3\CMS\Backend\Form\Container\InlineControlContainer
{

    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderPossibleRecordsSelectorTypeGroupDB(array $inlineConfiguration)
    {
        $selector = parent::renderPossibleRecordsSelectorTypeGroupDB($inlineConfiguration);
        if(getenv('ADMIRALCLOUD_DISABLE_FILEUPLOAD') == 1) {
            foreach ($this->requireJsModules as $key => $module) {
                if (isset($module['TYPO3/CMS/Backend/DragUploader'])) {
                    unset($this->requireJsModules[$key]);
                    $regex = '/<a href="#" class="btn btn-default t3js.drag.uploader.*?\/a>/s';
                    if (preg_match($regex, $selector, $matches)) {
                        $selector = preg_replace($regex, '', $selector);
                    }
                }
            }
        }
        if ($this->displayAdmiralCloudButton()) {
            $button = $this->renderAdmiralCloudOverviewButton($inlineConfiguration);

            // Inject button before help-block
            if (strpos($selector, '</div><div class="help-block">') > 0) {
                $selector = str_replace('</div><div class="help-block">', $button . '</div><div class="help-block">', $selector);
            // Try to inject it into the form-control container
            } elseif (preg_match('/<\/div><\/div>$/i', $selector)) {
                $selector = preg_replace('/<\/div><\/div>$/i', $button . '</div></div>', $selector);
            } else {
                $selector .= $button;
            }
            $button = $this->renderAdmiralCloudUploadButton($inlineConfiguration);

            // Inject button before help-block
            if (strpos($selector, '</div><div class="help-block">') > 0) {
                $selector = str_replace('</div><div class="help-block">', $button . '</div><div class="help-block">', $selector);
            // Try to inject it into the form-control container
            } elseif (preg_match('/<\/div><\/div>$/i', $selector)) {
                $selector = preg_replace('/<\/div><\/div>$/i', $button . '</div></div>', $selector);
            } else {
                $selector .= $button;
            }
        }

        return $selector;
    }

    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderAdmiralCloudOverviewButton(array $inlineConfiguration): string
    {
        $languageService = $this->getLanguageService();

        if (!$this->admiralCloudStorageAvailable()) {
            $errorTextHtml = [];
            $errorTextHtml[] = '<div class="alert alert-danger" style="display: inline-block">';
            $errorTextHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
            $errorTextHtml[] = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.error-no-storage-access'));
            $errorTextHtml[] = '</div>';

            return LF . implode(LF, $errorTextHtml);
        }

        $groupFieldConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config'];

        $foreign_table = $inlineConfiguration['foreign_table'];
        $allowedAssetTypes = ConfigurationUtility::getAssetTypesByAllowedElements($groupFieldConfiguration['appearance']['elementBrowserAllowed']);
        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid']);

        $element = 'admiral_cloud' . $this->inlineData['config'][$currentStructureDomObjectIdPrefix]['md5'];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $compactViewUrl = $uriBuilder->buildUriFromRoute('admiral_cloud_browser_show', [
            'element' => $element,
            'irreObject' => $currentStructureDomObjectIdPrefix . '-' . $foreign_table,
            'assetTypes' => implode(',', $allowedAssetTypes)
        ]);

        $this->requireJsModules[] = 'TYPO3/CMS/AdmiralCloudConnector/Browser';
        $buttonText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.button'));
        $titleText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.header'));

        $buttonHtml = [];
        $buttonHtml[] = '<a href="#" class="btn btn-default t3js-admiral_cloud-browser-btn overview ' . $element . '"'
            . ' data-admiral_cloud-browser-url="' . htmlspecialchars($compactViewUrl) . '" '
            . ' data-title="' . htmlspecialchars($titleText) . '">';
        $buttonHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
        $buttonHtml[] = $buttonText;
        $buttonHtml[] = '</a>';
        return LF . implode(LF, $buttonHtml);
    }

    /**
     * @param array $inlineConfiguration
     * @return string
     */
    protected function renderAdmiralCloudUploadButton(array $inlineConfiguration): string
    {
        $languageService = $this->getLanguageService();

        if (!$this->admiralCloudStorageAvailable()) {
            $errorTextHtml = [];
            $errorTextHtml[] = '<div class="alert alert-danger" style="display: inline-block">';
            $errorTextHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
            $errorTextHtml[] = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.error-no-storage-access'));
            $errorTextHtml[] = '</div>';

            return LF . implode(LF, $errorTextHtml);
        }

        $groupFieldConfiguration = $inlineConfiguration['selectorOrUniqueConfiguration']['config'];

        $foreign_table = $inlineConfiguration['foreign_table'];
        $allowedAssetTypes = ConfigurationUtility::getAssetTypesByAllowedElements($groupFieldConfiguration['appearance']['elementBrowserAllowed']);
        $currentStructureDomObjectIdPrefix = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid']);

        $element = 'admiral_cloud' . $this->inlineData['config'][$currentStructureDomObjectIdPrefix]['md5'];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $compactViewUrl = $uriBuilder->buildUriFromRoute('admiral_cloud_browser_upload', [
            'element' => $element,
            'irreObject' => $currentStructureDomObjectIdPrefix . '-' . $foreign_table,
            'assetTypes' => implode(',', $allowedAssetTypes)
        ]);

        $this->requireJsModules[] = 'TYPO3/CMS/AdmiralCloudConnector/Browser';
        $buttonText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.uploadbutton'));
        $titleText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.header'));

        $buttonHtml = [];
        $buttonHtml[] = '<a href="#" class="btn btn-default t3js-admiral_cloud-browser-btn upload ' . $element . '"'
            . ' data-admiral_cloud-browser-url="' . htmlspecialchars($compactViewUrl) . '" '
            . ' data-title="' . htmlspecialchars($titleText) . '">';
        $buttonHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
        $buttonHtml[] = $buttonText;
        $buttonHtml[] = '</a>';
        return LF . implode(LF, $buttonHtml);
    }

    /**
     * Check if the BE user has access to the AdmiralCloud storage
     *
     * Admin has access when there is a resource storage with driver type AdmiralCloud
     * Editors need to have access to a mount of that storage
     *
     * @return bool
     */
    protected function admiralCloudStorageAvailable(): bool
    {
        /** @var ResourceStorage $fileStorage */
        foreach ($this->getBackendUserAuthentication()->getFileStorages() as $fileStorage) {
            if ($fileStorage->getDriverType() === AdmiralCloudDriver::KEY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the BE user has access to the AdmiralCloud browser
     *
     * Admin has access when there is a resource storage with driver type AdmiralCloud
     * Editors need to have access to a mount of that storage
     *
     * @return bool
     */
    protected function displayAdmiralCloudButton(): bool
    {
        $backendUser = $this->getBackendUserAuthentication();
        $filePermissions = $backendUser->getFilePermissions();

        return $backendUser->isAdmin() || (bool)$filePermissions['addFileViaAdmiralCloud'];
    }
}
