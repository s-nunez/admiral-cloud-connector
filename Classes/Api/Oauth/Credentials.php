<?php

/**
 *
 * Copyright (c) Bynder. All rights reserved.
 *
 * Licensed under the MIT License. For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// src/Bynder/Api/Impl/Oauth/Credentials.php
namespace CPSIT\AdmiralcloudConnector\Api\Oauth;

/**
 * Class to hold Oauth tokens necessary for every API request.
 */
class Credentials
{

    /**
     * @var string Access key.
     */
    private $accessKey;
    /**
     * @var string Access Secret.
     */
    private $accessSecret;
    /**
     * @var string Access token.
     */
    private $clientId;




    /**
     * Initialises a new instance with the specified params.
     *
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $token
     * @param string $tokenSecret
     */
    public function __construct()
    {
        $this->accessKey = getenv('ADMIRALCLOUD_ACCESS_KEY');
        $this->accessSecret = getenv('ADMIRALCLOUD_ACCESS_SECRET');
        $this->clientId = getenv('ADMIRALCLOUD_CLIENT_ID');
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     */
    public function setAccessKey(string $accessKey)
    {
        $this->accessKey = $accessKey;
    }

    /**
     * @return string
     */
    public function getAccessSecret(): string
    {
        return $this->accessSecret;
    }

    /**
     * @param string $accessSecret
     */
    public function setAccessSecret(string $accessSecret)
    {
        $this->accessSecret = $accessSecret;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }
}
