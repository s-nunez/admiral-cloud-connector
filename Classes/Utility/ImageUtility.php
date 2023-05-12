<?php

namespace CPSIT\AdmiralCloudConnector\Utility;

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Class ImageUtility
 * @package CPSIT\AdmiralCloudConnector\Utility
 */
class ImageUtility
{
    /**
     * Calculate dimension based on image ratio and cropped data
     *
     * For instance you have the original width, height and new width.
     * And want to calculate the new height with the same ratio as the original dimensions
     *
     * WARNING: Don't forget to set setTxAdmiralCloudConnectorCrop for the file before if the crop information is wanted
     *
     * @param FileInterface $file
     * @param integer|string $width
     * @param integer|string $height
     * @param integer|string $maxWidth
     * @param integer|string $maxHeight
     * @return \stdClass [width, height]
     */
    public static function calculateDimensions(
        FileInterface $file,
        $width = null,
        $height = null,
        $maxWidth = null,
        $maxHeight = null
    ): \stdClass {

        $width = (int)$width;
        $height = (int)$height;
        $maxWidth = (int) $maxWidth;
        $maxHeight = (int) $maxHeight;
        $originalWidth = static::getFileWidthWithCropInformation($file);
        $originalHeight = static::getFileHeightWithCropInformation($file);

        // Create object to be returned
        $finalDimensions = new \stdClass();
        $finalDimensions->width = $width;
        $finalDimensions->height = $height;

        // If width and height are not set, get dimensions from original file
        if ($finalDimensions->width === 0 && $finalDimensions->height === 0) {
            $finalDimensions->width = $originalWidth;
            $finalDimensions->height = $originalHeight;
        }

        // Adjust dimensions for maximal width and height
        $finalDimensions = static::adjustDimensionsForMaxWidth($maxWidth, $finalDimensions);
        $finalDimensions = static::adjustDimensionsForMaxHeight($maxHeight, $finalDimensions);

        // Set height if is not defined
        if ($finalDimensions->height === 0 && $finalDimensions->width > 0) {
            $finalDimensions->height = (int)floor($finalDimensions->width / $originalWidth * $originalHeight);
            $finalDimensions = static::adjustDimensionsForMaxHeight($maxHeight, $finalDimensions);
        }

        // Set width if is not defined
        if ($finalDimensions->width === 0 && $finalDimensions->height > 0) {
            $finalDimensions->width = (int)floor($finalDimensions->height / $originalHeight * $originalWidth);
            $finalDimensions = static::adjustDimensionsForMaxWidth($maxWidth, $finalDimensions);
        }

        return $finalDimensions;
    }

    /**
     * @param FileInterface $file
     * @return int
     */
    protected static function getFileWidthWithCropInformation(FileInterface $file): int
    {
        $crop = static::getCropInformation($file);

        if (!empty($crop)) {
            $fileWidth = (int) $crop->cropData->width;
        }

        if (empty($fileWidth)) {
            $fileWidth = (int)$file->getProperty('width');
        }

        return $fileWidth;
    }

    /**
     * @param FileInterface $file
     * @return int
     */
    protected static function getFileHeightWithCropInformation(FileInterface $file): int
    {
        $crop = static::getCropInformation($file);

        if (!empty($crop)) {
            $fileHeight = (int) $crop->cropData->height;
        }

        if (empty($fileHeight)) {
            $fileHeight = (int)$file->getProperty('height');
        }

        return $fileHeight;
    }

    /**
     * Adjust dimensions for maxWidth
     *
     * @param int $maxWidth
     * @param \stdClass $dimensions
     * @return \stdClass
     */
    protected static function adjustDimensionsForMaxWidth(int $maxWidth, \stdClass $dimensions): \stdClass
    {
        if ($maxWidth && $dimensions->width > $maxWidth) {
            if ($dimensions->width) {
                $dimensions->height = (int)floor($maxWidth / $dimensions->width * $dimensions->height);
            }

            $dimensions->width = $maxWidth;
        }

        return $dimensions;
    }

    /**
     * Adjust dimensions for maxHeight
     *
     * @param int $maxHeight
     * @param \stdClass $dimensions
     * @return \stdClass
     */
    protected static function adjustDimensionsForMaxHeight(int $maxHeight, \stdClass $dimensions): \stdClass
    {
        if ($maxHeight && $dimensions->height > $maxHeight) {
            if ($dimensions->width) {
                $dimensions->width = (int)floor($maxHeight / $dimensions->height * $dimensions->width);
            }

            $dimensions->height = $maxHeight;
        }

        return $dimensions;
    }

    /**
     * @param FileInterface $file
     * @return \stdClass|null
     */
    protected static function getCropInformation(FileInterface $file): ?\stdClass
    {
        if (is_callable([$file, 'getTxAdmiralCloudConnectorCrop'])) {
            $crop = $file->getTxAdmiralCloudConnectorCrop();
        } else {
            $crop = $file->getProperty('tx_admiralcloudconnector_crop');
        }

        if (!is_string($crop)) {
            return null;
        }

        return json_decode($crop) ?: null;
    }
}
