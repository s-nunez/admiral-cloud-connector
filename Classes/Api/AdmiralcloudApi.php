<?php

namespace CPSIT\AdmiralcloudConnector\Api;
use CPSIT\AdmiralcloudConnector\Api\Oauth\Credentials;
use CPSIT\AdmiralcloudConnector\Api\Oauth\OauthRequestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

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
     * @var string code token
     */
    protected $code;

    /**
     * @var string device
     */
    protected $device;

    /**
     * Initialises a new instance of the class.
     *
     * @param OauthRequestHandler $requestHandler Instance of the request handler used to communicate with the API.
     *
     */
    public function __construct(OauthRequestHandler $requestHandler,$code)
    {
        $this->baseUrl = getenv('ADMIRALCLOUD_BASE_URL');
        $this->requestHandler = $requestHandler;
        $this->code = $code;
        $this->device = md5($GLOBALS['BE_USER']->user['id']);

    }

    /**
     * Creates an instance of AdmiralcloudApi
     *
     * @return AdmiralcloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function create($settings){
        $credentials = new Credentials();
        if (self::validateSettings($credentials)) {
            $curl = curl_init();

            $state = '0.' . base_convert(self::random() . '00', 10, 36);
            #$state = '0.abcdefghi';
            $params = [
                "accessSecret" => $credentials->getAccessSecret(),
                "controller" => $settings['controller'],
                "action" => $settings['action'],
                "payload" => [
                    "email" => $GLOBALS['BE_USER']->user['email'],
                    "firstname" => $GLOBALS['BE_USER']->user['realName'],
                    "lastname" => $GLOBALS['BE_USER']->user['realName'],
                    "state" => $state,
                    "client_id" => $credentials->getClientId(),
                    "callbackUrl" => base64_encode($settings['callbackUrl'])
                ]
            ];
            #var_dump($params);
            $signedValues = self::acSignatureSign($params);
            #$signedValues['hash'] = '6514cba9eadd8492cb9dbda4a66a7082880fb206513f33db35754b08891f2568';
            #var_dump($signedValues);
            $payload = json_encode($params['payload']);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://authdev.admiralcloud.com/v4/login/app?poc=true",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($params['payload']),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "x-admiralcloud-accesskey: " . $credentials->getAccessKey(),
                    "x-admiralcloud-rts: " . $signedValues['timestamp'],
                    "x-admiralcloud-hash: " . $signedValues['hash'],
                    "x-admiralcloud-debugsignature: 1",
                    "x-admiralcloud-clientid: " . $credentials->getClientId(),
                    "x-admiralcloud-device: " . md5($GLOBALS['BE_USER']->user['id'])


                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $codeParams = [
                'state' => $params['payload']['state'],
                'device' => md5($GLOBALS['BE_USER']->user['id']),
                'client_id' => $credentials->getClientId()

            ];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://authdev.admiralcloud.com/v4/requestCode?" . http_build_query($codeParams),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);


            $code = json_decode($response);

            $requestClient = new Client([]);
            $requestHandler = \CPSIT\AdmiralcloudConnector\Api\Oauth\OauthRequestHandler::create($credentials, '', $requestClient);
            return new AdmiralcloudApi($requestHandler, $code->code);
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralcloudApi service creation are not valid.");
        }
    }

    public static function acSignatureSign($params)
    {
        $accessSecret = $params['accessSecret'];
        if (!$accessSecret) return 'accessSecretMissing';
        $controller = $params['controller'];
        if (!$controller) return 'controllerMissing';
        $action = $params['action'];
        if (!$action) return 'actionMissing';
        $data = $params['payload'];
        if (!$data) return 'payloadMustBeObject';

        ksort($data);
        $payload = [];
        foreach ($data as $key => $value) {
            $payload[$key] = $data[$key];
        }
        #var_dump(json_encode($payload));
        $ts = time();
        #$ts = '1583770833';
        #echo 'ts: ' . $ts . PHP_EOL;
        $valueToHash = strtolower($params['controller']) . PHP_EOL .
            strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . '{}');
        #echo 'valueToHash: ' . $valueToHash . PHP_EOL;
        $hash = hash_hmac('sha256', $valueToHash, $accessSecret);
        #echo 'hash: ' . $hash . PHP_EOL;
        return [
            'hash' => $hash,
            'timestamp' => $ts
        ];
    }

    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function random()
    {
        return (float)rand() / (float)getrandmax();
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return OauthRequestHandler
     */
    public function getRequestHandler(): OauthRequestHandler
    {
        return $this->requestHandler;
    }

    /**
     * @param OauthRequestHandler $requestHandler
     */
    public function setRequestHandler(OauthRequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getDevice(): string
    {
        return $this->device;
    }

    /**
     * @param string $device
     */
    public function setDevice(string $device)
    {
        $this->device = $device;
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
