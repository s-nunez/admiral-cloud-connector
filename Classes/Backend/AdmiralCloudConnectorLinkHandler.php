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
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler;

/**
 * Class AdmiralCloudLinkHandler
 * @package CPSIT\AdmiralCloudConnector\Backend
 */
class AdmiralCloudConnectorLinkHandler implements \CPSIT\AdmiralCloudConnector\Backend\LinkHandlerInterface
{
   protected $linkAttributes = ['target', 'title', 'class', 'params', 'rel'];
   protected $view;
   protected $configuration;

   /**
   * Initialize the handler
   *
   * @param \CPSIT\AdmiralCloudConnector\Controller\Backend\AbstractLinkBrowserController $linkBrowser
   * @param string $identifier
   * @param array $configuration Page TSconfig
   */
   public function initialize(\CPSIT\AdmiralCloudConnector\Controller\Backend\AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
   {
      $this->linkBrowser = $linkBrowser;
      $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
      $this->view = GeneralUtility::makeInstance(StandaloneView::class);
      $this->view->getRequest()->setControllerExtensionName('AdmiralCloudConnector');
      $this->view->setLayoutRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Layouts')]);
      $this->view->setTemplateRootPaths([GeneralUtility::getFileAbsFileName('EXT:admiral_cloud_connector/Resources/Private/Templates/LinkBrowser')]);
      $this->configuration = $configuration;
   }

   /**
   * Checks if this is the handler for the given link
   *
   * Also stores information locally about currently linked issue
   *
   * @param array $linkParts Link parts as returned from TypoLinkCodecService
   *
   * @return bool
   */
   public function canHandleLink(array $linkParts)
   {
        if (!$linkParts['url']) {
            return false;
        }
        if (isset($linkParts['url'][$this->mode]) && $linkParts['url'][$this->mode] instanceof $this->expectedClass) {
            if(GeneralUtility::isFirstPartOfStr($linkParts['url'][$this->mode]->getMimeType(), 'admiralCloud/')){
                $this->linkParts = $linkParts;
                return true;
            }
        }
        return false;
   }

   /**
   * Format the current link for HTML output
   *
   * @return string
   */
   public function formatCurrentUrl(): string
   {
        return $this->linkParts['url'][$this->mode]->getName();
   }


   /**
   * Render the link handler
   *
   * @param ServerRequestInterface $request
   *
   * @return string
   */
   public function render(ServerRequestInterface $request): string
   {
      GeneralUtility::makeInstance(PageRenderer::class)
         ->loadRequireJsModule('TYPO3/CMS/AdmiralCloudConnector/Browser');

      $languageService = $GLOBALS['LANG'];

      $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
      $compactViewUrl = $uriBuilder->buildUriFromRoute('admiral_cloud_browser_rte_link');

      $rteLinkDownloadLabel = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:linkHandler.rteLinkDownload'));
      $buttonText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.button'));
      $titleText = htmlspecialchars($languageService->sL('LLL:EXT:admiral_cloud_connector/Resources/Private/Language/locallang_be.xlf:browser.header'));

      $buttonHtml = [];
      $buttonHtml[] = '<div style="text-align: center;margin-top: 1rem;">'
            . '<span style="display:none"><input id="rteLinkDownload" type="checkbox" style="margin-right: 0.5rem; position: relative; top: 2px;"/>' . $rteLinkDownloadLabel . '</span></div>'
            . '<a href="#" class="btn btn-default t3js-admiral_cloud-browser-btn rte-link"'
            . ' style="margin: 2rem auto;"'
            . ' data-admiral_cloud-browser-url="' . htmlspecialchars($compactViewUrl) . '" '
            . ' data-title="' . htmlspecialchars($titleText) . '">';
      $buttonHtml[] = $this->iconFactory->getIcon('actions-admiral_cloud-browser', Icon::SIZE_SMALL)->render();
      $buttonHtml[] = $buttonText;
      $buttonHtml[] = '</a>';

      $this->view->assign('html', LF . implode(LF, $buttonHtml));
      return $this->view->render('AdmiralCloud');
   }

   /**
   * @return string[] Array of body-tag attributes
   */
   public function getBodyTagAttributes(): array
   {
    return [
        'data-current-link' => GeneralUtility::makeInstance(LinkService::class)->asString(['type' => LinkService::TYPE_FILE, 'file' => $this->linkParts['url']['file']])
    ];
   }

   /**
   * @return array
   */
   public function getLinkAttributes()
   {
      return $this->linkAttributes;
   }

   /**
   * @param string[] $fieldDefinitions Array of link attribute field definitions
   * @return string[]
   */
   public function modifyLinkAttributes(array $fieldDefinitions)
   {
      return $fieldDefinitions;
   }

   /**
   * We don't support updates since there is no difference to simply set the link again.
   *
   * @return bool
   */
   public function isUpdateSupported()
   {
      return FALSE;
   }
}