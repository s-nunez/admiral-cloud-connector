<?php

namespace CPSIT\AdmiralcloudConnector\Controller\Backend;
use CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Form\Controller\AbstractBackendController;

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
        $this->view->setTemplate('Show');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->admiralcloudService = $objectManager->get(AdmiralcloudService::class);
        $parameters = $request->getQueryParams();
        $settings = [
            'callbackUrl' => 'https://t3intpoc.admiralcloud.com/overview?cmsOrigin=' . base64_encode('http://' . $_SERVER['HTTP_HOST']),
            'controller' => 'login',
            'action' => 'app'
        ];
        $admiralcloudApi = $this->admiralcloudService->getAdmiralcloudApi($settings);

        $this->view->assignMultiple([
            'iframeUrl' => $settings['callbackUrl'] . '&code=' . $admiralcloudApi->getCode(),
            'parameters' => [
                'element' => $parameters['element'],
                'irreObject' => $parameters['irreObject'],
                'assetTypes' => $parameters['assetTypes']
            ]
        ]);
        $response->getBody()->write($this->view->render());

        return $response;
    }

    /**
     * Makes the AJAX call to expand or collapse the foldertree.
     * Called by an AJAX Route, see AjaxRequestHandler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function authAction(): ResponseInterface
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->admiralcloudService = $objectManager->get(AdmiralcloudService::class);
        $settings = [
            'callbackUrl' => 'https://t3intpoc.admiralcloud.com/overview?cmsOrigin=' . base64_encode('http//' . $_SERVER['HTTP_HOST'])
        ];
        $admiralcloudApi = $this->admiralcloudService->getAdmiralcloudApi($settings);

        header('Content-type: application/json');
        $data = [
            'code' => $admiralcloudApi->getCode()
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
        header('Content-type: application/json');
        $data = [
            'media' => $media,
            'target' => $target
        ];
        echo json_encode($data);
        die();
        /*
        try {
            $files = [];
            $storage = $this->getBynderStorage();
            $indexer = $this->getIndexer($storage);

            foreach ($request->getParsedBody()['files'] ?? [] as $fileIdentifier) {
                $file = $storage->getFile($fileIdentifier);
                if ($file instanceof File) {
                    // (Re)Fetch metadata
                    $indexer->extractMetaData($file);
                    $files[] = $file->getUid();
                }
            }

            if ($files === []) {
                return $this->createJsonResponse($response, ['error' => 'No files given/found'], 406);
            }

            return $this->createJsonResponse($response, ['files' => $files], 201);
        } catch (Exception $e) {
            return $this->createJsonResponse($response, [
                'error' => 'The interaction with Bynder contained conflicts. Please contact the webmasters.',
                'exception' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ],
            ], 404);
        }
        */
    }
}
