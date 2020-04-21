<?php


namespace CPSIT\AdmiralCloudConnector\Backend\ToolbarItems;

use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AdmiralCloudToolbarItem
 * @package CPSIT\AdmiralCloudConnector\Backend\ToolbarItems
 */
class AdmiralCloudToolbarItem implements ToolbarItemInterface
{

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getItem()
    {
        $extensionName = 'admiral_cloud_connector';

        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath($extensionName) . 'Resources/Private/Templates/Backend/ToolbarMenu/MenuItem.html'
        );

        $request = $standaloneView->getRequest();
        $request->setControllerExtensionName($extensionName);

        return $standaloneView->render();
    }

    /**
     * @inheritDoc
     */
    public function hasDropDown()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getDropDown()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalAttributes()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIndex()
    {
        return 50;
    }
}