<?php

namespace CPSIT\AdmiralCloudConnector\Resource\Rendering;

use CPSIT\AdmiralCloudConnector\Exception\InvalidAssetException;
use CPSIT\AdmiralCloudConnector\Service\AdmiralCloudService;
use CPSIT\AdmiralCloudConnector\Service\TagBuilderService;
use CPSIT\AdmiralCloudConnector\Traits\AssetFactory;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class AssetRenderer
 * @package CPSIT\AdmiralCloudConnector\Resource\Rendering
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
            if (GeneralUtility::isFirstPartOfStr($file->getMimeType(), 'admiralCloud/')) {
                $asset = $this->getAsset($file->getIdentifier());
                return $asset->isImage($file->getStorage()->getUid()) || $asset->isDocument() || $asset->isAudio() || $asset->isVideo();
            }
        } catch (InvalidAssetException $e) {
        }
        return false;
    }

    /**
     * Render for given File(Reference) HTML output
     *
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false): string
    {
        if (!($file instanceof File) && is_callable([$file, 'getOriginalFile'])) {
            $originalFile = $file->getOriginalFile();
        } else {
            $originalFile = $file;
        }

        $asset = $this->getAsset($originalFile->getIdentifier());
        switch (true) {
            case $asset->isImage($originalFile->getStorage()->getUid()):
            case $asset->isDocument():
                return $this->renderImageTag($file, $width, $height, $options, $usedPathsRelativeToCurrentScript);

            case $asset->isVideo():
                return $this->renderVideoTag($file, $width, $height, $options, $usedPathsRelativeToCurrentScript);

            case $asset->isAudio():
                return $this->renderAudioTag($file, $width, $height, $options, $usedPathsRelativeToCurrentScript);

            default:
                throw new InvalidAssetException('No rendering implemented for this asset.', 1558540658478);
        }
    }

    /**
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript
     * @return string
     */
    protected function renderVideoTag(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false): string
    {
        return $this->getPlayerHtml($file, $width, $height, $options);
    }

    /**
     * Render HTML5 <audio> tag with VideoJS capabilities
     *
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript
     * @return string
     */
    protected function renderAudioTag(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false): string
    {
        return $this->getPlayerHtml($file, $width, $height, $options);
    }

    /**
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript
     * @return string
     */
    protected function renderImageTag(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false): string
    {
        $tag = $this->getTagBuilder('img', $options);

        $tag->addAttribute(
            'src',
            $this->getAdmiralCloudService()->getImagePublicUrl($file, (int)$width, (int)$height),
            $usedPathsRelativeToCurrentScript
        );

        if ((int)$width > 0) {
            $tag->addAttribute('width', !empty($width) ? $width : null);
        }
        if ((int)$height > 0) {
            $tag->addAttribute('height', !empty($height) ? $height : null);
        }

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if ($tag->hasAttribute('alt') === false) {
            $tag->addAttribute('alt', $file->getProperty('alternative'));
        }
        if ($tag->hasAttribute('title') === false) {
            $tag->addAttribute('title', $file->getProperty('title'));
        }
        return $tag->render();
    }

    /**
     * Get iframe with AdmiralCloud player
     *
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @return string
     */
    protected function getPlayerHtml(FileInterface $file, $width, $height, array $options = []): string
    {
        if (is_callable([$file, 'getOriginalFile'])) {
            $originalFile = $file->getOriginalFile();
        } else {
            $originalFile = $file;
        }

        $tag = $this->getTagBuilder('iframe', $options);

        $tag->addAttribute('src', $this->getAdmiralCloudService()->getPlayerPublicUrl($originalFile));
        $tag->addAttribute('allowfullscreen', true);

        if ((int)$width > 0) {
            $tag->addAttribute('width', !empty($width) ? $width : null);
        }
        if ((int)$height > 0) {
            $tag->addAttribute('height', !empty($height) ? $height : null);
        }

        if ($tag->hasAttribute('title') === false) {
            $tag->addAttribute('title', $file->getProperty('title'));
        }

        return $tag->render();
    }

    /**
     * Return an instance of TagBuilderService
     *
     * @param string $type
     * @param array $options
     * @return TagBuilder
     */
    protected function getTagBuilder(string $type, array $options): TagBuilder
    {
        $tagBuilderService = GeneralUtility::makeInstance(TagBuilderService::class);
        $tag = $tagBuilderService->getTagBuilder($type);
        $tagBuilderService->initializeAbstractTagBasedAttributes($tag, $options);
        return $tag;
    }

    /**
     * @return AdmiralCloudService
     */
    protected function getAdmiralCloudService(): AdmiralCloudService
    {
        return GeneralUtility::makeInstance(AdmiralCloudService::class);
    }
}
