<?php

namespace CPSIT\AdmiralcloudConnector\Controller\Backend;
use CPSIT\Service\AdmiralcloudService;
use TYPO3\CMS\Form\Controller\AbstractBackendController;

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
class BrowserController extends AbstractBackendController
{
    /**
     * locationRepository
     *
     * @var AdmiralcloudService
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $admiralcloudService = null;

    /**
     * action show
     *
     */
    public function showAction()
    {

        $admiralcloudApi = $this->admiralcloudService->getAdmiralcloudApi();
        $curl = curl_init();

        $credentials = [
            "accessSecret" => getenv('ADMIRALCLOUD_ACCESS_SECRET'),
            "accessKey" => getenv('ADMIRALCLOUD_ACCESS_KEY'),
            "client_id" => getenv('ADMIRALCLOUD_CLIENT_ID')
        ];
        $state = '0.' . base_convert($this->random() . '00', 10, 36);
        #$state = '123';
        $params = [
            "accessSecret" => $credentials['accessSecret'],
            "controller" => "login",
            "action" => "app",
            "payload" => [
                "email" => "typo3.test@mmpro.de",
                "firstname" => "Jane",
                "lastname" => "Doe",
                "state" => $state,
                "client_id" => $credentials['client_id'],
                "callbackUrl" => "aHR0cHM6Ly90M2ludHBvYy5hZG1pcmFsY2xvdWQuY29tL292ZXJ2aWV3P2Ntc09yaWdpbj1hSFIwY0hNNkx5OTNaV0p6YVhSbFpHVnRieTVoWkcxcGNtRnNZMnh2ZFdRdVkyOXQ="
            ]
        ];
        #var_dump($params);
        $signedValues = $this->acSignatureSign($params);
        $signedValues['hash'] = '6514cba9eadd8492cb9dbda4a66a7082880fb206513f33db35754b08891f2568';
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
            "x-admiralcloud-accesskey: " . $credentials['accessKey'],
            "x-admiralcloud-rts: " . $signedValues['timestamp'],
            "x-admiralcloud-hash: " . $signedValues['hash'],
            "x-admiralcloud-debugsignature: 1",
            "x-admiralcloud-clientid: " . $credentials['client_id'],
            "x-admiralcloud-device: WzI0LDI0LDE5MjAsMTA4MCwiV2luMzIiXQ=="


          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }

        $codeParams = [
            'state' => $params['payload']['state'],
            'device' => 'WzI0LDI0LDE5MjAsMTA4MCwiV2luMzIiXQ==',
            'client_id' => $credentials['client_id']

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

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }

    }

    public function acSignatureSign($params){
        $accessSecret = $params['accessSecret'];
        if(!$accessSecret) return 'accessSecretMissing';
        $controller = $params['controller'];
        if(!$controller) return 'controllerMissing';
        $action = $params['action'];
        if(!$action) return 'actionMissing';
        $data = $params['payload'];
        if(!$data) return 'payloadMustBeObject';

        ksort($data);
        $payload = [];
        foreach ($data as $key=>$value){
            $payload[$key] = $data[$key];
        }
        #var_dump(json_encode($payload));
        $ts = time();
        $valueToHash = strtolower($params['controller']) . PHP_EOL .
            strtolower($params['action']) . PHP_EOL . $ts . (empty($payload) ? '' : PHP_EOL . json_encode($payload));
        #var_dump($valueToHash);
        $hash = hash_hmac('sha256', $valueToHash, $accessSecret);
        return [
            $hash,
            'timestamp' => $ts
        ];
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function random()
    {
        return (float)rand() / (float)getrandmax();
    }
}
