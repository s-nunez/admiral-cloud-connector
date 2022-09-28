<?php


namespace CPSIT\AdmiralCloudConnector\ViewHelpers;

use CPSIT\AdmiralCloudConnector\Resource\Rendering\AssetRenderer;
use CPSIT\AdmiralCloudConnector\Traits\AdmiralCloudStorage;
use CPSIT\AdmiralCloudConnector\Utility\ImageUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImageViewHelper
 * @package CPSIT\AdmiralCloudConnector\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    use AdmiralCloudStorage;

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('txAdmiralCloudCrop', 'string', 'AdmiralCloud crop information', false, '');
    }

    public function render()
    {
        if(version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.4.0', '<')){
            if (($this->arguments['src'] === null && $this->arguments['image'] === null) || ($this->arguments['src'] !== null && $this->arguments['image'] !== null)) {
                 throw new \Exception('You must either specify a string src or a File object.', 1382284106);
             }
        } else {
            if (($this->arguments['src'] === '' && $this->arguments['image'] === null) || ($this->arguments['src'] !== '' && $this->arguments['image'] !== null)) {
                 throw new \Exception('You must either specify a string src or a File object.', 1382284106);
             }
        }

        $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);

        if (!($image instanceof File) && is_callable([$image, 'getOriginalFile'])) {
            $originalFile = $image->getOriginalFile();
        } else {
            $originalFile = $image;
        }

        if ($originalFile->getStorage()->getUid() === $this->getAdmiralCloudStorage()->getUid()) {
            if ($this->arguments['txAdmiralCloudCrop']) {
                $originalFile->setTxAdmiralCloudConnectorCrop($this->arguments['txAdmiralCloudCrop']);
            }

            $dimensions = ImageUtility::calculateDimensions(
                $image,
                $this->arguments['width'],
                $this->arguments['height'],
                $this->arguments['maxWidth'],
                $this->arguments['maxHeight']
            );

            /** @var AssetRenderer $assetRenderer */
            $assetRenderer = GeneralUtility::makeInstance(AssetRenderer::class);
            return $assetRenderer->render($image, $dimensions->width, $dimensions->height, [], false, $this->tag);
        }

        return parent::render();
    }
}
