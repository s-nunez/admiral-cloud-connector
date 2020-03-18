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

    public function render()
    {
        if (($this->arguments['src'] === null && $this->arguments['image'] === null) || ($this->arguments['src'] !== null && $this->arguments['image'] !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1382284106);
        }

        $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);

        if (!($image instanceof File) && is_callable([$image, 'getOriginalFile'])) {
            $image = $image->getOriginalFile();
        } else {
            $image = $image;
        }

        if ($image->getStorage()->getUid() === $this->getAdmiralCloudStorage()->getUid()) {
            $width = $this->arguments['width'];

            if (!$width) {
                $width = $this->arguments['maxWidth'];
            }

            $height = $this->arguments['height'];

            if (!$height) {
                $height = $this->arguments['maxHeight'];
            }

            /** @var AssetRenderer $assetRenderer */
            $assetRenderer = GeneralUtility::makeInstance(AssetRenderer::class);
            return $assetRenderer->render($image, $width, $height);
        }

        return parent::render();

    }
}