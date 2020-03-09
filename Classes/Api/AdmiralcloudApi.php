<?php

namespace CPSIT\AdmiralcloudConnector\Api;
use CPSIT\AdmiralcloudConnector\Api\Oauth\Credentials;
use CPSIT\AdmiralcloudConnector\Api\Oauth\OauthRequestHandler;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2020
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class AdmiralcloudApi
{
    /**
     * @var string Base Url necessary for API calls.
     */
    protected $baseUrl;

    /**
     * @var OauthRequestHandler Instance of the Oauth request handler.
     */
    protected $requestHandler;

    /**
     * Initialises a new instance of the class.
     *
     * @param string $baseUrl Base Url used for all the requests to the API.
     * @param OauthRequestHandler $requestHandler Instance of the request handler used to communicate with the API.
     *
     */
    public function __construct($baseUrl, OauthRequestHandler $requestHandler)
    {
        $this->baseUrl = getenv('ADMIRALCLOUD_BASE_URL');
        $this->requestHandler = $requestHandler;
    }

    /**
     * Creates an instance of AdmiralcloudApi
     *
     * @return AdmiralcloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public function create(){
        $credentials = new Credentials();
        if (self::validateSettings($credentials)) {
            $stack = HandlerStack::create(new CurlHandler());
            $stack->push(
                new Oauth1([
                    'consumer_key' => $credentials->getConsumerKey(),
                    'consumer_secret' => $credentials->getConsumerSecret(),
                    'token' => $credentials->getToken(),
                    'token_secret' => $credentials->getTokenSecret(),
                    'request_method' => Oauth1::REQUEST_METHOD_HEADER,
                    'signature_method' => Oauth1::SIGNATURE_METHOD_HMAC
                ])
            );

            $requestOptions = [
                'base_uri' => $this->baseUrl,
                'handler' => $stack,
                'auth' => 'oauth',
            ];

            // Configures request Client (adding proxy, etc.)
            #if (isset($settings['requestOptions']) && is_array($settings['requestOptions'])) {
            #    $requestOptions += $settings['requestOptions'];
            #}

            $requestClient = new Client($requestOptions);
            $requestHandler = \CPSIT\AdmiralcloudConnector\Api\Oauth\OauthRequestHandler::create($credentials, $this->baseUrl, $requestClient);
            return new AdmiralcloudApi($this->baseUrl, $requestHandler);
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralcloudApi service creation are not valid.");
        }
    }

    /**
     * Checks if the settings array passed is valid.
     * @param Credentials $credentials
     * @return bool Whether the settings array is valid.
     */
    protected static function validateSettings($credentials)
    {
        return $credentials->getAccessKey() && $credentials->getAccessSecret() && $credentials->getClientId();
    }
}
