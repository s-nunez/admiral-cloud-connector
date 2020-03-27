<?php

namespace CPSIT\AdmiralcloudConnector\Controller\Backend;
use CPSIT\AdmiralcloudConnector\Resource\Index\FileIndexRepository;
use CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService;
use CPSIT\AdmiralcloudConnector\Traits\AdmiralcloudStorage;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
    use AdmiralcloudStorage;

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
    protected $templateRootPaths = ['EXT:admiralcloud_connector/Resources/Private/Templates/Backend/Browser'];

    /**
     * PartialRootPath
     *
     * @var string[]
     */
    protected $partialRootPaths = ['EXT:admiralcloud_connector/Resources/Private/Partials/Backend/Browser'];

    /**
     * LayoutRootPath
     *
     * @var string[]
     */
    protected $layoutRootPaths = ['EXT:admiralcloud_connector/Resources/Private/Layouts/Backend/Browser'];

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * CompactViewController constructor.
     */
    public function __construct()
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setLayoutRootPaths($this->layoutRootPaths);
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
     * locationRepository
     *
     * @var \CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $admiralcloudService = null;

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function showAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        return $this->prepareShowUpload($request, $response,'https://t3intpoc.admiralcloud.com/overview?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function uploadAction(ServerRequestInterface $request = NULL, ResponseInterface $response = NULL): ResponseInterface
    {
        return $this->prepareShowUpload($request, $response, 'https://t3intpoc.admiralcloud.com/upload/files?cmsOrigin=');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $callbackUrl
     * @return ResponseInterface
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function prepareShowUpload(ServerRequestInterface $request, ResponseInterface $response, string $callbackUrl){
        $this->view->setTemplate('Show');
        $parameters = $request->getQueryParams();

        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
        $path = $uriBuilder->buildUriFromRoute('ajax_admiralcloud_browser_auth');
        $this->view->assignMultiple([
            'ajaxUrl' => (string)$path,
            'iframeUrl' => $callbackUrl . base64_encode('http://' . $_SERVER['HTTP_HOST']),
            'parameters' => [
                'element' => $parameters['element'],
                'irreObject' => $parameters['irreObject'],
                'assetTypes' => $parameters['assetTypes']
            ]
        ]);
        $response->getBody()->write($this->view->render());
        return $response;
    }


    public function apiAction()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->admiralcloudService = $objectManager->get(AdmiralcloudService::class);

        $data = $this->admiralcloudService->getMediaInfo([33512]);
        #$data = $this->admiralcloudService->getMetaData([33512]);
        #header('Content-type: application/json');
        DebuggerUtility::var_dump($data);

        $data = $this->admiralcloudService->getSearch('716821 ');
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->admiralcloudService = $objectManager->get(AdmiralcloudService::class);
        $bodyParams = json_decode($request->getBody()->getContents());
        $settings = [
            'callbackUrl' => $bodyParams->callbackUrl,
            'controller' => 'login',
            'action' => 'app',
            'device' => $bodyParams->device
        ];
        $admiralcloudAuthCode = $this->admiralcloudService->getAdmiralcloudAuthCode($settings);

        header('Content-type: application/json');
        $data = [
            'code' => $admiralcloudAuthCode
        ];
        echo json_encode($data);
        die();
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

            $file = $storage->getFile($mediaContainer['id']);
            if ($file instanceof File) {
                $file->setTxAdmiralcloudconnectorLinkhashFromMediaContainer($mediaContainer);
                $file->setTypeFromMimeType($mediaContainer['type'] . '/' . $mediaContainer['fileExtension']);

                $this->getFileIndexRepository()->add($file);

                // (Re)Fetch metadata
                $indexer->extractMetaData($file);
                $files[] = $file->getUid();
            }

            if ($files === []) {
                return $this->createJsonResponse($response, ['error' => 'No files given/found'], 406);
            }

            return $this->createJsonResponse($response, ['files' => $files], 201);
        } catch (Exception $e) {
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
}
