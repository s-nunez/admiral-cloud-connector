<?php

/**
 * Licensed under the MIT License. For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CPSIT\AdmiralCloudConnector\Api;

use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApi;
use CPSIT\AdmiralCloudConnector\Exception\RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Static Factory class used to create instances of AdmiralCloudApi.
 */
class AdmiralCloudApiFactory
{
    /**
     * Creates an instance of AdmiralCloudApi using the given settings.
     *
     * @param array $settings
     * @param string $method
     * @return AdmiralCloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     * @throws RuntimeException
     */
    public static function create(array $settings,string $method = 'post'): AdmiralCloudApi
    {
        return AdmiralCloudApi::create($settings,$method);
    }

    /**
     * Creates an instance of AdmiralCloudApi using the given settings.
     *
     * @param array $settings
     * @return string
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     * @throws RuntimeException
     */
    public static function auth(array $settings): string
    {
        return AdmiralCloudApi::auth($settings);
    }

}
