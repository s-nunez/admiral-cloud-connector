<?php

namespace CPSIT\AdmiralCloudConnector\Utility;

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
        return 2000;
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
        if (!self::isProduction()) {
            return 'https://smartcropdev.admiralcloud.com/';
        }
        return 'https://images.admiralcloud.com/';
    }

    /**
     * @return string
     */
    public static function getImageUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
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
        if (!self::isProduction()) {
            return 'https://t3intpoc.admiralcloud.com/';
        }
        return getenv('ADMIRALCLOUD_IFRAMEURL') ?: 'https://t3prod.admiralcloud.com/';
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
    public static function getPlayerFileUrl(): string
    {
        $add = '';
        if (!self::isProduction()) {
            $add = 'dev';
        }

        return 'https://player' . $add . '.admiralcloud.com/?v=';
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
    public static function getImagePNGPlayerConfigId(): string
    {
        return getenv('ADMIRALCLOUD_IMAGE_PNG_CONFIG_ID') ?: 3;
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

    /**
     * @return string
     */
    public static function getMetaTitleField(): string
    {
        return getenv('ADMIRALCLOUD_METADATA_FIELD_OVERRIDE_title') ?: 'container_name';
    }

    /**
     * @return string
     */
    public static function getMetaAlternativeField(): string
    {
        return getenv('ADMIRALCLOUD_METADATA_FIELD_OVERRIDE_alternative') ?: 'meta_alttag';
    }

    /**
     * @return string
     */
    public static function getMetaDescriptionField(): string
    {
        return getenv('ADMIRALCLOUD_METADATA_FIELD_OVERRIDE_description') ?: 'container_description';
    }

    /**
     * @return string
     */
    public static function getMetaCopyrightField(): string
    {
        return getenv('ADMIRALCLOUD_METADATA_FIELD_OVERRIDE_copyright') ?: 'meta_iptc_copyrightNotice';
    }

    /**
     * Checks, if a given mime type is an AdmiralCloud svg mime type.
     *
     * @param string $mimeType The mime type to check for
     * @return bool            Whether it's an AdmiralCloud svg mime type or not
     */
    public static function isSvgMimeType(string $mimeType): bool
    {
        return (bool) preg_match('/^admiralCloud\/image\/svg(\+xml)?$/', $mimeType);
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getPlayerConfigurationIdByType(string $type): string
    {
        $pCId = self::getImagePlayerConfigId();
        switch ($type) {
            case 'audio':
                $pCId = self::getAudioPlayerConfigId();
                break;
            case 'video':
                $pCId = self::getVideoPlayerConfigId();
                break;
            case 'document':
                $pCId = self::getDocumentPlayerConfigId();
                break;
        }
        return $pCId;
    }

    public static  function getLocalFileUrl(){
        return '/filehub/deliverFile/';
    }
}
