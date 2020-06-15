<?php

namespace CPSIT\AdmiralCloudConnector\Controller\Backend;

use CPSIT\AdmiralCloudConnector\Resource\Index\FileIndexRepository;
use CPSIT\AdmiralCloudConnector\Service\AdmiralCloudService;
use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Form\Controller\AbstractBackendController;
use TYPO3\CMS\Core\Resource\File;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2020
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class BrowserController extends AbstractBackendController
{
    use AdmiralCloudStorage;

    /**
     * Fluid Standalone View
     *
     * @var StandaloneView
     */
    protected $view;

    /**
     * TemplateRootPath
     *
     * @var string[]
     */
    protected $templateRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Templates/Browser'];

    /**
     * PartialRootPath
     *
     * @var string[]
     */
    protected $partialRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Partials/Browser'];

    /**
     * LayoutRootPath
     *
     * @var string[]
     */
    protected $layoutRootPaths = ['EXT:admiral_cloud_connector/Resources/Private/Layouts/Browser'];

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * AdmiralCloud service
     *
     * @var AdmiralCloudService
     */
    protected $admiralCloudService = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CompactViewController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setLayoutRootPaths($this->layoutRootPaths);
        $this->admiralCloudService = GeneralUtility::makeInstance(AdmiralCloudService::class);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        if ($view instanceof BackendTemplateView) {
            parent::initializeView($view);
        }
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function showAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        return $this->prepareIframe($request, $response,ConfigurationUtility::getIframeUrl() . 'overview?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function uploadAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        return $this->prepareIframe($request, $response, ConfigurationUtility::getIframeUrl() . 'upload/files?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function cropAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        $this->view->assignMultiple([
            'mediaContainerId' => $request->getQueryParams()['mediaContainerId'],
            'embedLink' => $request->getQueryParams()['embedLink'],
            'modus' => 'crop'
        ]);
        return $this->prepareIframe($request, $response, ConfigurationUtility::getIframeUrl() . 'overview?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function rteLinkAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        $this->view->assign('modus', 'rte-link');
        return $this->prepareIframe($request, $response,ConfigurationUtility::getIframeUrl() . 'overview?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $callbackUrl
     * @return ResponseInterface
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function prepareIframe(ServerRequestInterface $request, ResponseInterface $response, string $callbackUrl){
        $this->view->setTemplate('Show');
        $parameters = $request->getQueryParams();

        $protocol = 'http';
        if ((isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] === 'on')
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            $protocol = 'https';
        }
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
        $path = $uriBuilder->buildUriFromRoute('ajax_admiral_cloud_browser_auth');
        $this->view->assignMultiple([
            'iframeHost' => rtrim(ConfigurationUtility::getIframeUrl(),'/'),
            'ajaxUrl' => (string)$path,
            'iframeUrl' => $callbackUrl . base64_encode($protocol .'://' . $_SERVER['HTTP_HOST']),
            'parameters' => [
                'element' => $parameters['element'],
                'irreObject' => $parameters['irreObject'],
            ]
        ]);
        $response->getBody()->write($this->view->render());
        return $response;
    }


    public function apiAction()
    {
        $data = $this->admiralCloudService->getMetaData([33512]);
        #$data = $this->admiralCloudService->getMetaData([33512]);
        #header('Content-type: application/json');
        DebuggerUtility::var_dump($data);

        $data = $this->admiralCloudService->getSearch('716821 ');
        #header('Content-type: application/json');
        DebuggerUtility::var_dump($data);
        #var_dump($data);
        die();
    }

    /**
     * Makes the AJAX call to expand or collapse the foldertree.
     * Called by an AJAX Route, see AjaxRequestHandler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function authAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $bodyParams = json_decode($request->getBody()->getContents());
        $settings = [
            'callbackUrl' => $bodyParams->callbackUrl,
            'controller' => 'login',
            'action' => 'app',
            'device' => $bodyParams->device
        ];

        try {
            $admiralCloudAuthCode = $this->admiralCloudService->getAdmiralCloudAuthCode($settings);
            return $this->createJsonResponse(
                $response,
                [
                    'code' => $admiralCloudAuthCode
                ],
                200
            );
        } catch (\Throwable $exception) {
            $this->logger->error('The authentication to AdmiralCloud was not possible.', ['exception' => $exception]);
            return $this->createJsonResponse($response, [
                'error' => 'Error information: ' . $exception->getMessage(),
                'exception' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ],
            ], 500);
        }
    }

    /**
     * Action: Retrieve file from storage
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getFilesAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $media = $request->getParsedBody()['media'];
        $target = $request->getParsedBody()['target'];

        try {
            $files = [];
            $storage = $this->getAdmiralCloudStorage();
            $indexer = $this->getIndexer($storage);
            $mediaContainer = $media['mediaContainer'];

            // First of all check that the file contain a valid hash in other case an exception would be thrown
            $linkHash = $this->admiralCloudService->getLinkHashFromMediaContainer($mediaContainer);

            $file = $storage->getFile($mediaContainer['id']);
            if ($file instanceof File) {
                $file->setTxAdmiralCloudConnectorLinkhash($linkHash);
                $file->setTypeFromMimeType($mediaContainer['type'] . '/' . $mediaContainer['fileExtension']);

                $this->getFileIndexRepository()->add($file);

                // (Re)Fetch metadata
                $indexer->extractMetaData($file);

                $this->storeInSessionCropInformation($file, $media);

                $files[] = $file->getUid();
            }

            if ($files === []) {
                return $this->createJsonResponse($response, ['error' => 'No files given/found'], 406);
            }

            return $this->createJsonResponse($response, ['files' => $files], 201);
        } catch (Exception $e) {
            $this->logger->error('Error adding file from AdmiralCloud.', ['exception' => $e]);
            return $this->createJsonResponse($response, [
                'error' => 'The interaction with AdmiralCloud contained conflicts. Please contact the webmasters.',
                'exception' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ],
            ], 404);
        }
    }

    /**
     * Action: Retrieve file from storage
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getMediaPublicUrlAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $media = $request->getParsedBody()['media'];

        try {
            $mediaContainer = $media['mediaContainer'];

            // Get link hash for media container
            $linkHash = $this->admiralCloudService->getLinkHashFromMediaContainer($mediaContainer);

            /** @var AdmiralCloudService $admiralCloudService */
            $admiralCloudService = GeneralUtility::makeInstance(AdmiralCloudService::class);

            return $this->createJsonResponse($response, [
                'publicUrl' => $admiralCloudService->getDirectPublicUrlForHash($linkHash)
            ], 200);
        } catch (Exception $e) {
            $this->logger->error('Error adding file from AdmiralCloud.', ['exception' => $e]);
            return $this->createJsonResponse($response, [
                'error' => 'The interaction with AdmiralCloud contained conflicts. Please contact the webmasters.',
                'exception' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ],
            ], 404);
        }
    }

    /**
     * Action: Retrieve file from storage
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function cropFileAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $media = $request->getParsedBody()['media'];
        $target = $request->getParsedBody()['target'];
        $cropperData = $media['cropperData'];
        unset($cropperData['smartCropperUrl'], $cropperData['smartCropperUrlAOI']);
        $cropperData = json_encode($cropperData);


        try {
            $storage = $this->getAdmiralCloudStorage();
            $mediaContainer = $media['mediaContainer'];
            $file = $storage->getFile($mediaContainer['id']);
            $file->setTxAdmiralCloudConnectorCrop($cropperData);
            $link = $this->admiralCloudService->getImagePublicUrl($file,226,150);

            return $this->createJsonResponse($response, ['target' => $target,'cropperData' => $cropperData,'link' => $link], 201);
        } catch (Exception $e) {
            $this->logger->error('Error cropping file from AdmiralCloud.', ['exception' => $e]);
            return $this->createJsonResponse($response, [
                'error' => 'The interaction with AdmiralCloud contained conflicts. Please contact the webmasters.',
                'exception' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ],
            ], 404);
        }
    }

    /**
     * Store in BE session the crop information for given file
     *
     * @param FileInterface $file
     * @param array $media
     */
    protected function storeInSessionCropInformation(FileInterface $file, array $media): void
    {
        if (!empty($media['cropperData'])) {
            $cropperData = $media['cropperData'];
            unset($cropperData['smartCropperUrl'], $cropperData['smartCropperUrlAOI']);

            $sessionData = $this->getBackendUser()->getSessionData('admiralCloud') ?? [];
            $sessionData['cropInformation'][$file->getUid()] = $cropperData;
            $this->getBackendUser()->setAndSaveSessionData('admiralCloud', $sessionData);
        }
    }

    /**
     * Gets the Indexer.
     *
     * @param ResourceStorage $storage
     * @return Indexer
     */
    protected function getIndexer(ResourceStorage $storage): Indexer
    {
        return GeneralUtility::makeInstance(Indexer::class, $storage);
    }

    /**
     * @return FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return FileIndexRepository::getInstance();
    }

    /**
     * @param ResponseInterface $response
     * @param array|null $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function createJsonResponse(ResponseInterface $response, $data, int $statusCode): ResponseInterface
    {
        $response = $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        if (!empty($data)) {
            $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES;
            $response->getBody()->write(json_encode($data ?: null, $options));
            $response->getBody()->rewind();
        }

        return $response;
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
