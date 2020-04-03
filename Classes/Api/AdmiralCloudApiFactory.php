<?php

/**
 * Copyright (c) Bynder. All rights reserved.
 * Licensed under the MIT License. For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// src/Bynder/Api/BynderApiFactory.php
namespace CPSIT\AdmiralCloudConnector\Api;

use CPSIT\AdmiralCloudConnector\Api\AdmiralCloudApi;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Static Factory class used to create instances of BynderApi.
 */
class AdmiralCloudApiFactory
{

    /**
     * Creates an instance of BynderApi using the given settings.
     *
     * @return AdmiralCloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function create($settings)
    {
        return AdmiralCloudApi::create($settings);
    }

    /**
     * Creates an instance of BynderApi using the given settings.
     *
     * @return AdmiralCloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function auth($settings)
    {
        return AdmiralCloudApi::auth($settings);
    }

}
