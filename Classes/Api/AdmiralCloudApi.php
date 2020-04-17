<?php

namespace CPSIT\AdmiralCloudConnector\Api;

use CPSIT\AdmiralCloudConnector\Api\Oauth\Credentials;
use CPSIT\AdmiralCloudConnector\Exception\InvalidPropertyException;
use CPSIT\AdmiralCloudConnector\Exception\RuntimeException;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class AdmiralCloudApi
{
    /**
     * @var string Base Url necessary for API calls.
     */
    protected $baseUrl;

    /**
     * @var string code token
     */
    protected $code;

    /**
     * @var string device
     */
    protected $device;

    /**
     * @var string data
     */
    protected $data;

    /**
     * Initialises a new instance of the class.
     *
     * @param string $requestHandler Instance of the request handler used to communicate with the API.
     *
     */
    public function __construct($data)
    {
        $this->baseUrl = getenv('ADMIRALCLOUD_BASE_URL');
        $this->device = md5($GLOBALS['BE_USER']->user['id']);
        $this->data = $data;
    }

    /**
     * Creates an instance of AdmiralCloudApi
     *
     * @param array $settings
     * @return AdmiralCloudApi instance.
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public static function create(array $settings)
    {
        $credentials = new Credentials();
        if (self::validateSettings($credentials)) {
            $curl = curl_init();

            $params = [
                "accessSecret" => $credentials->getAccessSecret(),
                "controller" => $settings['controller'],
                "action" => $settings['action'],
                "payload" => $settings['payload']
            ];

            $signedValues = self::acSignatureSign($params,'v5');

            $routeUrl = ConfigurationUtility::getApiUrl() . 'v5/' . $settings['route'];

            curl_setopt_array($curl, array(
                CURLOPT_URL => $routeUrl,
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
                    "x-admiralcloud-hash: " . $signedValues['hash']
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Log error
            if (!$httpCode || $httpCode >= 400) {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->error(sprintf(
                    'Error in AdmiralCloud route process. URL: %s. HTTP code: %d. Error message: %s',
                    $routeUrl,
                    $httpCode,
                    $response ?: $err
                ));

                throw new RuntimeException('Error in AdmiralCloud route process. HTTP Code: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE));
            }

            curl_close($curl);

            return new AdmiralCloudApi($response);
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralCloudApi service creation are not valid.");
        }
    }

    /**
     * Creates an instance of AdmiralCloudApi
     *
     * @param array $settings
     * @return string
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function auth(array $settings): string
    {
        $credentials = new Credentials();
        $device = md5($GLOBALS['BE_USER']->user['id']);
        if(isset($settings['device'])){
            $device = $settings['device'];
        }
        if (self::validateSettings($credentials)) {
            $curl = curl_init();

            $state = '0.' . base_convert(self::random() . '00', 10, 36);
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
            $signedValues = self::acSignatureSign($params);

            $loginUrl = ConfigurationUtility::getAuthUrl() . "v4/login/app?poc=true";

            curl_setopt_array($curl, array(
                CURLOPT_URL => $loginUrl,
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
                    "x-admiralcloud-device: " . $device


                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Log error
            if (!$httpCode || $httpCode >= 400) {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->error(sprintf(
                    'Error in AdmiralCloud login process. URL: %s. HTTP Code: %d. Error message: %s',
                    $loginUrl,
                    $httpCode,
                    $response ?: $err
                ));

                throw new RuntimeException('Error in AdmiralCloud login process. HTTP Code: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE));
            }

            curl_close($curl);

            $codeParams = [
                'state' => $params['payload']['state'],
                'device' => $device,
                'client_id' => $credentials->getClientId()

            ];

            $authUrl = ConfigurationUtility::getAuthUrl() . "v4/requestCode?" . http_build_query($codeParams);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $authUrl,
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
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Log error
            if (!$httpCode || $httpCode >= 400) {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->error(sprintf(
                    'Error in AdmiralCloud auth process. URL: %s. HTTP Code: %d. Error message: %s',
                    $authUrl,
                    $httpCode,
                    $response ?: $err
                ));

                throw new RuntimeException('Error in AdmiralCloud auth process. HTTP Code: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE));
            }

            curl_close($curl);

            $code = json_decode($response);

            if ($response && !$code) {
                $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                $logger->error('Error decoding JSON from auth response. JSON: ' . $response);

                throw new RuntimeException('Error decoding JSON from auth response.');
            }

            if (empty($code->code)) {
                throw new RuntimeException('There is not any code in the response of the AUTH process');
            }

            return $code->code;
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralCloudApi service creation are not valid.");
        }
    }

    public static function acSignatureSign($params, $version='v4')
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

        $ts = time();

        if ($version !== 'v4' && $version !== 'v5') {
            throw new InvalidArgumentException('Version for acSignatureSign should be v4 or v5. Version given: ' . $version);
        }

        if ($version === 'v4') {
            $valueToHash = strtolower($params['controller']) . PHP_EOL .
                strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . '{}');
        }
        if ($version === 'v5') {
            $valueToHash = strtolower($params['controller']) . PHP_EOL .
                strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . json_encode($payload));
        }

        $hash = hash_hmac('sha256', $valueToHash, $accessSecret);

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
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data)
    {
        $this->data = $data;
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
