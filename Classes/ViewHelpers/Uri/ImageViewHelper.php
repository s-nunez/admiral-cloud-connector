<?php
namespace CPSIT\AdmiralCloudConnector\ViewHelpers\Uri;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use CPSIT\AdmiralCloudConnector\Resource\Rendering\AssetRenderer;
use CPSIT\AdmiralCloudConnector\Service\AdmiralCloudService;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Resizes a given image (if required) and returns its relative path.
 *
 * Examples
 * ========
 *
 * Default
 * -------
 *
 * ::
 *
 *    <f:uri.image src="EXT:myext/Resources/Public/typo3_logo.png" />
 *
 * Results in the following output within TYPO3 frontend:
 *
 * ``typo3conf/ext/myext/Resources/Public/typo3_logo.png``
 *
 * and the following output inside TYPO3 backend:
 *
 * ``../typo3conf/ext/myext/Resources/Public/typo3_logo.png``
 *
 * Image Object
 * ------------
 *
 * ::
 *
 *    <f:uri.image image="{imageObject}" />
 *
 * Results in the following output within TYPO3 frontend:
 *
 * ``fileadmin/images/image.png``
 *
 * and the following output inside TYPO3 backend:
 *
 * ``fileadmin/images/image.png``
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *    {f:uri.image(src: 'EXT:myext/Resources/Public/typo3_logo.png', minWidth: 30, maxWidth: 40)}
 *
 * ``typo3temp/assets/images/[b4c0e7ed5c].png``
 *
 * Depending on your TYPO3s encryption key.
 *
 * Non existing image
 * ------------------
 *
 * ::
 *
 *    <f:uri.image src="NonExistingImage.png" />
 *
 * ``Could not get image resource for "NonExistingImage.png".``
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('txAdmiralCloudCrop', 'string', 'AdmiralCloud crop information', false, '');
    }

    /**
     * Resizes the image (if required) and returns its path. If the image was not resized, the path will be equal to $src
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws Exception
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $src = $arguments['src'];
        $image = $arguments['image'];
        $treatIdAsReference = $arguments['treatIdAsReference'];

        if (($src === null && $image === null) || ($src !== null && $image !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1460976233);
        }

        $imageService = self::getImageService();
        $originalImage = $imageService->getImage($src, $image, $treatIdAsReference);

        if (!($originalImage instanceof File) && is_callable([$originalImage, 'getOriginalFile'])) {
            $image = $originalImage->getOriginalFile();
        } else {
            $image = $originalImage;
        }

        if ($image->getType() === File::FILETYPE_IMAGE
            && GeneralUtility::isFirstPartOfStr($image->getMimeType(), 'admiralCloud/')) {
            $crop = $arguments['txAdmiralCloudCrop'];

            if ($crop) {
                $image->setTxAdmiralCloudConnectorCrop($arguments['txAdmiralCloudCrop']);
            }

            if (!$crop && $originalImage->getProperty('tx_admiralcloudconnector_crop')) {
                $image->setTxAdmiralCloudConnectorCrop($originalImage->getProperty('tx_admiralcloudconnector_crop'));
            }

            $fileImageWidth = $image->_getMetaData()['width'];
            $fileImageHeight = $image->_getMetaData()['height'];

            $width = $arguments['width'];

            if (!$width) {
                $width = $arguments['maxWidth'];
            }

            if (!$width) {
                $width = 0;
            }

            $height = $arguments['height'];

            if (!$height) {
                $height = $arguments['maxHeight'];
            }

            if (!$height) {
                $height = 0;
            }

            if ($fileImageWidth && $fileImageHeight) {
                if (!$height && $width) {
                    $height = round(($width / $fileImageWidth) * $fileImageHeight);
                }

                if (!$width && $height) {
                    $width = round(($height / $fileImageHeight) * $fileImageWidth);
                }
            }

            /** @var AdmiralCloudService $admiralCloudService */
            $admiralCloudService = GeneralUtility::makeInstance(AdmiralCloudService::class);
            return $admiralCloudService->getImagePublicUrl($image, $width, $height);
        }
        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }

    /**
     * Return an instance of ImageService using object manager
     *
     * @return ImageService
     */
    protected static function getImageService()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager->get(ImageService::class);
    }
}
