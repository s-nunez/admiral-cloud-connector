<?php


namespace CPSIT\AdmiralcloudConnector\ViewHelpers;

use CPSIT\AdmiralcloudConnector\Resource\Rendering\AssetRenderer;
use CPSIT\AdmiralcloudConnector\Traits\AdmiralcloudStorage;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImageViewHelper
 * @package CPSIT\AdmiralcloudConnector\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    use AdmiralcloudStorage;

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
        if (($this->arguments['src'] === null && $this->arguments['image'] === null) || ($this->arguments['src'] !== null && $this->arguments['image'] !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1382284106);
        }

        $file = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);

        if (!($file instanceof File) && is_callable([$file, 'getOriginalFile'])) {
            $image = $file->getOriginalFile();
        } else {
            $image = $file;
        }

        if ($image->getStorage()->getUid() === $this->getAdmiralCloudStorage()->getUid()) {
            $crop = $this->arguments['txAdmiralCloudCrop'];

            if ($crop) {
                $image->setTxAdmiralcloudconnectorCrop($this->arguments['txAdmiralCloudCrop']);
            }

            if (!$crop && $file->getProperty('tx_admiralcloudconnector_crop')) {
                $image->setTxAdmiralcloudconnectorCrop($file->getProperty('tx_admiralcloudconnector_crop'));
            }

            $width = $this->arguments['width'];

            if (!$width) {
                $width = $this->arguments['maxWidth'];
            }

            if (!$width) {
                $width = 0;
            }

            $height = $this->arguments['height'];

            if (!$height) {
                $height = $this->arguments['maxHeight'];
            }

            if (!$height) {
                $height = 0;
            }
            if (!$height) {
                $height = round(($width / $image->_getMetaData()['width']) * $image->_getMetaData()['height']);
            }
            if (!$width) {
                $width = round(($height / $image->_getMetaData()['height']) * $image->_getMetaData()['width']);
            }

            /** @var AssetRenderer $assetRenderer */
            $assetRenderer = GeneralUtility::makeInstance(AssetRenderer::class);
            return $assetRenderer->render($image, $width, $height);
        }

        return parent::render();

    }
}
