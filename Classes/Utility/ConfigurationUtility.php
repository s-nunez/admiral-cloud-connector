<?php

namespace CPSIT\AdmiralCloudConnector\Utility;



use CPSIT\AdmiralCloudConnector\Exception\InvalidExtensionConfigurationException;
use CPSIT\AdmiralCloudConnector\Resource\Asset;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility as CoreConfigurationUtility;

/**
 * Utility: Configuration
 * @package CPSIT\AdmiralCloudConnector\Utility
 */
class ConfigurationUtility
{
    const EXTENSION = 'admiral_cloud_connector';

    /**
     * @return int
     */
    public static function getDefaultImageWidth(): int
    {
        // TODO set default image width
        return 1200;
    }

    /**
     * @param string $allowedElements
     * @return array
     * @throws InvalidExtensionConfigurationException
     */
    public static function getAssetTypesByAllowedElements($allowedElements): array
    {
        $assetTypes = [];
        if (empty($allowedElements)) {
            // Defaults to only image & video
            $assetTypes = [Asset::TYPE_IMAGE, Asset::TYPE_VIDEO];
        } else {
            $allowed = GeneralUtility::trimExplode(',', strtolower($allowedElements), true);
            $possibilities = [
                Asset::TYPE_IMAGE => (self::getExtensionConfiguration())['asset_type_image'] ?? 'jpg,png,gif',
                Asset::TYPE_VIDEO => (self::getExtensionConfiguration())['asset_type_video'] ?? 'mp4,mov',
                Asset::TYPE_AUDIO => (self::getExtensionConfiguration())['asset_type_audio'] ?? 'mp3,wav',
                Asset::TYPE_DOCUMENT => (self::getExtensionConfiguration())['asset_type_document'] ?? 'pdf, doc, docx',
            ];
            foreach (array_filter($possibilities) as $key => $elements) {
                foreach (GeneralUtility::trimExplode(',', $elements, true) as $element) {
                    if (in_array($element, $allowed, true)) {
                        $assetTypes[] = $key;
                        break;
                    }
                }
            }
        }

        return $assetTypes;
    }

    /**
     * @param boolean $relativeToCurrentScript
     * @return string
     * @throws InvalidExtensionConfigurationException
     */
    public static function getUnavailableImage($relativeToCurrentScript = false): string
    {
        $path = GeneralUtility::getFileAbsFileName(
            (self::getExtensionConfiguration())['image_unavailable'] ??
            'EXT:admiral_cloud_connector/Resources/Public/Icons/ImageUnavailable.svg'
        );

        return ($relativeToCurrentScript) ? PathUtility::getAbsoluteWebPath($path) : str_replace(PATH_site, '', $path);
    }

    /**
     * @return array
     * @throws InvalidExtensionConfigurationException
     */
    public static function getBynderApiFactoryCredentials(): array
    {
        $credentials = [
            'baseUrl' => static::getApiBaseUrl(),
            'consumerKey' => (self::getExtensionConfiguration())['consumer_key'] ?? '',
            'consumerSecret' => (self::getExtensionConfiguration())['consumer_secret'] ?? '',
            'token' => (self::getExtensionConfiguration())['token_key'] ?? '',
            'tokenSecret' => (self::getExtensionConfiguration())['token_secret'] ?? '',
        ];
        return $credentials;
    }

    /**
     * @return string
     * @throws InvalidExtensionConfigurationException
     */
    public static function getApiBaseUrl(): string
    {
        return static::cleanUrl((self::getExtensionConfiguration())['url']);
    }

    /**
     * @return string
     * @throws InvalidExtensionConfigurationException
     */
    public static function getOnTheFlyBaseUrl(): string
    {
        return static::cleanUrl((self::getExtensionConfiguration())['otf_base_url']);
    }

    /**
     * @return boolean
     * @throws InvalidExtensionConfigurationException
     */
    public static function isOnTheFlyConfigured(): bool
    {
        return !empty((self::getExtensionConfiguration())['otf_base_url']);
    }

    /**
     * @return array
     * @throws InvalidExtensionConfigurationException
     */
    public static function getExtensionConfiguration(): array
    {
        static $configuration;
        if ($configuration === null) {
            $configuration = [
                'url' => '',
                'otf_base_url' => '',
                'consumer_key' => '',
                'consumer_secret' => '',
                'token_key' => '',
                'token_secret' => '',
                'image_unavailable' => 'EXT:admiral_cloud_connector/Resources/Public/Icons/ImageUnavailable.svg',
                'asset_type_image' => 'jpg,png,gif',
                'asset_type_video' => 'mp4'
            ];

            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            if (class_exists(CoreConfigurationUtility::class)) {
                $currentConfiguration = $objectManager->get(CoreConfigurationUtility::class)->getCurrentConfiguration('admiral_cloud_connector');
                $configuration = [];
                foreach ($currentConfiguration as $key => $value) {
                    $configuration[$key] = $value['value'];
                }
            } else {
                ArrayUtility::mergeRecursiveWithOverrule($configuration, (array)$objectManager->get(ExtensionConfiguration::class)->get('admiral_cloud_connector'));
            }

            /*
            if (empty($configuration['url']) ||
                empty($configuration['consumer_key']) || empty($configuration['consumer_secret']) ||
                empty($configuration['token_key']) || empty($configuration['token_secret'])
            ) {
                throw new InvalidExtensionConfigurationException('Make sure all Bynder oAuth settings are set in extension manager', 1519051718);
            }*/
        }
        return $configuration;
    }

    /**
     * Clean url
     *
     * When url given, make sure url is a valid url
     *
     * @param string $url
     * @return string
     */
    public static function cleanUrl(string $url): string
    {
        if ($url === '') {
            return $url;
        }

        // Make sure scheme is given
        $urlParts = parse_url($url);
        if (empty($urlParts['scheme'])) {
            $url = 'https://' . $url;
            $urlParts = parse_url($url);
        }

        // When there is a path make sure there is a leading slash
        if (!empty($urlParts['path'])) {
            $url = rtrim($url, '/') . '/';
        }

        return $url;
    }

    /**
     * @return bool
     */
    public static function isProduction(): bool
    {
        if (getenv('ADMIRALCLOUD_IS_PRODUCTION')) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public static function getApiUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }
        return 'https://api' . $add . '.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getAuthUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }
        return 'https://auth' . $add . '.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getSmartcropUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }
        return 'https://smartcrop' . $add . '.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getImageUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = '';
        }
        return 'https://images' . $add . '.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getThumbnailUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }
        return 'https://images' . $add . '.admiralcloud.com';
    }

    /**
     * @return string
     */
    public static function getIframeUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = '';
        }
        return 'https://t3intpoc' . $add . '.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getDirectFileUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }

        return 'https://filehub' . $add . '.admiralcloud.com/v5/deliverFile/';
    }

    /**
     * @return string
     */
    public static function getImagePlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_IMAGE_CONFIG_ID') ?: 3;
    }

    /**
     * @return string
     */
    public static function getVideoPlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_VIDEO_CONFIG_ID') ?: 2;
    }

    /**
     * @return string
     */
    public static function getDocumentPlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_DOCUMENT_CONFIG_ID') ?: 5;
    }

    /**
     * @return string
     */
    public static function getAudioPlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_AUDIO_CONFIG_ID') ?: 4;
    }

    /**
     * @return string
     */
    public static function getFlagPlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_FLAG_CONFIG_ID') ?: 0;
    }
}
