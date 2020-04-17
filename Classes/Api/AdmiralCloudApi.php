<?php

namespace CPSIT\AdmiralCloudConnector\Api;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use CPSIT\AdmiralCloudConnector\Api\Oauth\Credentials;
use CPSIT\AdmiralCloudConnector\Api\Oauth\AdmiralCloudRequestHandler;
use CPSIT\AdmiralCloudConnector\Utility\ConfigurationUtility;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
     * @var AdmiralCloudRequestHandler Instance of the Oauth request handler.
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
     * @return AdmiralCloudApi instance.
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
                "payload" => $settings['payload']
            ];

            $signedValues = self::acSignatureSign($params,'v5');

            curl_setopt_array($curl, array(
                CURLOPT_URL => ConfigurationUtility::getApiUrl() . 'v5/' . $settings['route'],
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

            curl_close($curl);

            return new AdmiralCloudApi($response);
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralCloudApi service creation are not valid.");
        }
    }

    /**
     * Creates an instance of AdmiralCloudApi
     *
     * @return AdmiralCloudApi instance.
     * @throws InvalidArgumentException Oauth settings not valid, consumer key or secret not in array.
     */
    public static function auth($settings)
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
                    "firstname" => $GLOBALS['BE_USER']->user['first_name'] ?: $GLOBALS['BE_USER']->user['realName'],
                    "lastname" => $GLOBALS['BE_USER']->user['last_name'] ?: $GLOBALS['BE_USER']->user['realName'],
                    "state" => $state,
                    "client_id" => $credentials->getClientId(),
                    "callbackUrl" => base64_encode($settings['callbackUrl']),
                    "settings" => [
                        "typo3group" => self::getSecurityGroup()
                    ]
                ]
            ];
            $signedValues = self::acSignatureSign($params);

            curl_setopt_array($curl, array(
                CURLOPT_URL => ConfigurationUtility::getAuthUrl() . "v4/login/app?poc=true",
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
            curl_close($curl);

            $codeParams = [
                'state' => $params['payload']['state'],
                'device' => $device,
                'client_id' => $credentials->getClientId()

            ];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => ConfigurationUtility::getAuthUrl() . "v4/requestCode?" . http_build_query($codeParams),
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
            return $code->code;
        } else {
            throw new InvalidArgumentException("Settings passed for AdmiralCloudApi service creation are not valid.");
        }
    }

    public static function acSignatureSign($params,$version='v4')
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
        if($version == 'v4'){
            $valueToHash = strtolower($params['controller']) . PHP_EOL .
                strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . '{}');
        }
        if($version == 'v5'){
            $valueToHash = strtolower($params['controller']) . PHP_EOL .
                strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . json_encode($payload));
        }
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

    public static function getSecurityGroup(){
        $groups = $GLOBALS['BE_USER']->user['usergroup_cached_list'];
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_admiralcloudconnector_security_groups');
        $queryBuilder->getRestrictions()->removeAll();
        $res = $queryBuilder->select('sg.*')
            ->from('tx_admiralcloudconnector_security_groups', 'sg')
            ->where(
                $queryBuilder->expr()->eq('sg.deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
            )
            ->execute();
        $sgs = [];
        while ($row = $res->fetch()) {
            $sgs[$row['ac_security_group_id']] = $row['be_groups'];
        }
        foreach ($sgs as $sgId=>$be_groups){
            $containsAllValues = !array_diff(explode(',', $be_groups), explode(',', $groups));
            if($containsAllValues){
                return implode('',explode(',',$be_groups));
            }
        }
        return '';
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
     * @return AdmiralCloudRequestHandler
     */
    public function getRequestHandler(): AdmiralCloudRequestHandler
    {
        return $this->requestHandler;
    }

    /**
     * @param AdmiralCloudRequestHandler $requestHandler
     */
    public function setRequestHandler(AdmiralCloudRequestHandler $requestHandler)
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
