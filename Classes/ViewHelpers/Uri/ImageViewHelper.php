<?php
namespace CPSIT\AdmiralcloudConnector\ViewHelpers\Uri;

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

use CPSIT\AdmiralcloudConnector\Resource\Rendering\AssetRenderer;
use CPSIT\AdmiralcloudConnector\Service\AdmiralcloudService;
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
        $this->registerArgument('src', 'string', 'src');
        $this->registerArgument('treatIdAsReference', 'bool', 'given src argument is a sys_file_reference record', false, false);
        $this->registerArgument('image', 'object', 'image');
        $this->registerArgument('crop', 'string|bool', 'overrule cropping of image (setting to FALSE disables the cropping set in FileReference)');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');

        $this->registerArgument('width', 'string', 'width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('minWidth', 'int', 'minimum width of the image');
        $this->registerArgument('minHeight', 'int', 'minimum height of the image');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
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
        $cropString = $arguments['crop'];
        $absolute = $arguments['absolute'];

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

        if (GeneralUtility::isFirstPartOfStr($image->getMimeType(), 'admiralcloud/')) {
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
            if (!$height) {
                $height = round(($width / $image->_getMetaData()['width']) * $image->_getMetaData()['height']);
            }
            if (!$width) {
                $width = round(($height / $image->_getMetaData()['height']) * $image->_getMetaData()['width']);
            }

            $admiracloudService = GeneralUtility::makeInstance(AdmiralcloudService::class);
            return $admiracloudService->getImagePublicUrl($image, $width, $height);
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
