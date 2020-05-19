<?php

namespace CPSIT\AdmiralCloudConnector\Backend;

use CPSIT\AdmiralCloudConnector\Exception\NotImplementedException;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;

/**
 * Class AdmiralCloudLinkHandler
 * @package CPSIT\AdmiralCloudConnector\Backend
 */
class AdmiralCloudLinkHandler extends AbstractLinkHandler implements LinkHandlerInterface
{
    /**
     * TemplateRootPath
     *
     * @var string[]
     */
    protected $templateRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Templates/Backend/Browser'];

    /**
     * PartialRootPath
     *
     * @var string[]
     */
    protected $partialRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Partials/Backend/Browser'];

    /**
     * LayoutRootPath
     *
     * @var string[]
     */
    protected $layoutRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Layouts/Backend/Browser'];

    /**
     * Initialize the handler
     *
     * @param AbstractLinkBrowserController $linkBrowser
     * @param string $identifier
     * @param array $configuration Page TSconfig
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function initialize(AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
    {
        $this->linkBrowser = $linkBrowser;
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $this->view->getRequest()->setControllerExtensionName(ConfigurationUtility::EXTENSION);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setLayoutRootPaths($this->layoutRootPaths);
    }

    /**
     * @inheritDoc
     */
    public function canHandleLink(array $linkParts)
    {
        // It is always false because render return a public url
        // It is not possible to come back to admiralCloud tab
        return false;
    }

    /**
     * @inheritDoc
     */
    public function formatCurrentUrl()
    {
        throw new NotImplementedException('This function is not need it. If you need it. Please implement it.');
    }

    /**
     * @inheritDoc
     */
    public function render(ServerRequestInterface $request)
    {
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('TYPO3/CMS/AdmiralCloudConnector/Browser');

        $languageService = $this->getLanguageService();

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $compactViewUrl = $uriBuilder->buildUriFromRoute('admiral_cloud_browser_rte_link');

        $buttonText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.button'));
        $titleText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.header'));

        $buttonHtml = [];
        $buttonHtml[] = '<a href="#" class="btn btn-default t3js-admiral_cloud-browser-btn rte-link"'
            . ' style="margin: 2rem auto;"'
            . ' data-admiral_cloud-browser-url="' . htmlspecialchars($compactViewUrl) . '" '
            . ' data-title="' . htmlspecialchars($titleText) . '">';
        $buttonHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
        $buttonHtml[] = $buttonText;
        $buttonHtml[] = '</a>';
        return LF . implode(LF, $buttonHtml);
    }

    /**
     * @inheritDoc
     */
    public function getBodyTagAttributes()
    {
        return [];
    }
}
