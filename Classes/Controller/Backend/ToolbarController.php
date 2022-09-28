<?php

namespace CPSIT\AdmiralCloudConnector\Controller\Backend;

use CPSIT\AdmiralCloudConnector\Service\MetadataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Controller\AbstractBackendController;

/**
 * Class ToolbarController
 * @package CPSIT\AdmiralCloudConnector\Controller\Backend
 */
class ToolbarController extends AbstractBackendController
{
    /**
     * Update metadata for changed files in AdmiralCloud
     * This function will be called per Ajax in the toolbar
     *
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function updateChangedMetadataAction(
        ServerRequestInterface $request = null
    ): ResponseInterface {
        $metadataService = $this->getMetadataService();
        $jsonArray = [];
        $statusCode = 200;
        try {
            $metadataService->updateLastChangedMetadatas();

            $jsonArray = ['message' => 'ok'];
        } catch (\Throwable $exception) {
            $jsonArray = ['message' => $exception->getMessage()];
            $statusCode = 500;
        }


        return new JsonResponse($jsonArray,$statusCode);
    }

    /**
     * @return MetadataService
     */
    protected function getMetadataService(): MetadataService
    {
        return GeneralUtility::makeInstance(MetadataService::class);
    }
}
