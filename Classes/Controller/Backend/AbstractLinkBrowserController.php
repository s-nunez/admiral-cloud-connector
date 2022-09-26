<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\AdmiralCloudConnector\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;

/**
 * Script class for the Link Browser window.
 * @internal This class is a specific Backend controller implementation and is not part of the TYPO3's Core API.
 */
abstract class AbstractLinkBrowserController extends \TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController
{
    /**
     * Injects the request object for the current request or subrequest
     * As this controller goes only through the main() method, it is rather simple for now
     *
     * @param ServerRequestInterface $request the current request
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->moduleTemplate->getDocHeaderComponent()->disable();
        $view = $this->moduleTemplate->getView();
        $view->setTemplate('LinkBrowser');
        $view->getRequest()->setControllerExtensionName('recordlist');
        $view->setTemplateRootPaths(['EXT:recordlist/Resources/Private/Templates/LinkBrowser/']);
        $view->setPartialRootPaths(['EXT:recordlist/Resources/Private/Partials/LinkBrowser/']);
        $view->setLayoutRootPaths(['EXT:backend/Resources/Private/Layouts/', 'EXT:recordlist/Resources/Private/Layouts/']);
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_misc.xlf');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_core.xlf');
        if($request->getQueryParams()['act'] == 'admiralCloud'){
            $this->moduleTemplate->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/AdmiralCloudConnector/Browser');
            $view->setTemplate('AdmiralCloud');
            $view = $this->moduleTemplate->getView();
            $view->getRequest()->setControllerExtensionName('AdmiralCloudConnector');
            $view->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Layouts')]);
            $view->setTemplateRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Templates/LinkBrowser')]);
        } 

        $this->determineScriptUrl($request);
        $this->initVariables($request);
        $this->loadLinkHandlers();
        $this->initCurrentUrl();

        $menuData = $this->buildMenuArray();
        $renderLinkAttributeFields = $this->renderLinkAttributeFields();
        if (method_exists($this->displayedLinkHandler, 'setView')) {
            $this->displayedLinkHandler->setView($view);
        }
        $browserContent = $this->displayedLinkHandler->render($request);
        if($request->getQueryParams()['act'] == 'admiralCloud'){
            $this->moduleTemplate->setContent($browserContent);
            $view->assign('html', $browserContent);
        }

        $this->initDocumentTemplate();
        
        $this->moduleTemplate->setTitle('Link Browser');
        if (!empty($this->currentLinkParts)) {
            $this->renderCurrentUrl();
        }

        $view->assign('menuItems', $menuData);
        $view->assign('linkAttributes', $renderLinkAttributeFields);
        $view->assign('contentOnly', $request->getQueryParams()['contentOnly'] ?? false);

        if ($request->getQueryParams()['contentOnly'] ?? false) {
            return new HtmlResponse($view->render());
        }
        if ($browserContent) {
            $view->assign('content', $browserContent);
        }
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

}
