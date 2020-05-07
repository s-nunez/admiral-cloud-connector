<?php

namespace CPSIT\AdmiralCloudConnector\Controller\Backend;

use CPSIT\AdmiralCloudConnector\Service\MetadataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    ): ResponseInterface {
        $metadataService = $this->getMetadataService();

        try {
            $metadataService->updateLastChangedMetadatas();

            $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->getBody()->write(json_encode(['message' => 'ok']));
        } catch (\Throwable $exception) {
            $response = $response->withStatus(500);
            $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->getBody()->write(json_encode(['message' => $exception->getMessage()]));
        }

        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @return MetadataService
     */
    protected function getMetadataService(): MetadataService
    {
        return GeneralUtility::makeInstance(MetadataService::class);
    }
}
