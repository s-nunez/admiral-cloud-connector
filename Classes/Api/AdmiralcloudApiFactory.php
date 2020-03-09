<?php

/**
 * Copyright (c) Bynder. All rights reserved.
 * Licensed under the MIT License. For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// src/Bynder/Api/BynderApiFactory.php
namespace Bynder\Api;

use CPSIT\AdmiralcloudConnector\Api\AdmiralcloudApi;
use InvalidArgumentException;

/**
 * Static Factory class used to create instances of BynderApi.
 */
class AdmiralcloudApiFactory
{

    /**
     * Creates an instance of BynderApi using the given settings.
     *
     * @return AdmiralcloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function create()
    {
        return AdmiralcloudApi::create();
    }

}
