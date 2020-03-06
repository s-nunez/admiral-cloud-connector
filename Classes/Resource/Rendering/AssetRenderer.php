<?php

namespace CPSIT\AdmiralcloudConnector\Resource\Rendering;

use CPSIT\AdmiralcloudConnector\Exception\InvalidAssetException;
use CPSIT\AdmiralcloudConnector\Traits\AssetFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AssetRenderer
 * @package CPSIT\AdmiralcloudConnector\Resource\Rendering
 */
class AssetRenderer implements FileRendererInterface
{
    use AssetFactory;

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 15;
    }

    /**
     * @inheritDoc
     */
    public function canRender(FileInterface $file)
    {
        try {
            if (GeneralUtility::isFirstPartOfStr($file->getMimeType(), 'admiralcloud/')) {
                $asset = $this->getAsset($file->getIdentifier());
                return $asset->isImage() || $asset->isDocument() || $asset->isAudio() || $asset->isVideo();
            }
        } catch (InvalidAssetException $e) {
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function render(
        FileInterface $file,
        $width,
        $height,
        array $options = [],
        $usedPathsRelativeToCurrentScript = false
    ) {
        // TODO: Implement render() method.
    }
}