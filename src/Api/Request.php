<?php

namespace Colfej\LeKioskCLI\Api;

use Colfej\LeKioskCLI\Configuration;
use GuzzleHttp\Client;

abstract class Request {

    const URL = 'https://api.lekiosk.com/api/v1/';
    const USER_AGENT = 'lekioskworld/552 CFNetwork/975.0.3 Darwin/18.2.0';
    const APP_VERSION = '3.11.2';
    const ACCESS_KEY_ID = 'iosp04';

    const GET_VERB = 'get';
    const POST_VERB = 'post';

    protected static $client;

    protected static function __request($verb, $url, $params = array(), $json = true) {

        if (is_null(self::$client)) {
            
            $config = Configuration::load();

            self::$client = new Client([
                'base_uri'  =>  self::URL,
                'auth'      =>  [$config['username'], $config['password']],
                'headers'   =>  [
                    'User-Agent'        =>  self::USER_AGENT,
                    'Authorization'     =>  'Basic '.base64_encode($config['username'].':'.$config['password']),
                    'appVersion'        =>  self::APP_VERSION,
                    'accessKeyId'       =>  self::ACCESS_KEY_ID
                ]
            ]);

        }

        switch ($verb) {
            case Request::GET_VERB:
                if (!empty($params)) {
                    $response = self::$client->get($url, ['query' => $params]);
                } else {
                    $response = self::$client->get($url);
                }
                break;
            
            case Request::POST_VERB:
                $response = self::$client->post($url, ['json' => $params]);
                break;

            default:
                throw new \Exception('HTTP verb "'.$verb.'" not allowed.');
                break;
        }

        if ($response->getStatusCode() != 200) {
            throw new \Exception('Error in response : '.$response->getBody());
        }

        $result = $response->getBody()->getContents();

        if ($json) {
            $result = json_decode($result, true);
        }

        return $result;
        
    }

    public static function get($url, $params = array()) {

        return self::__request(Request::GET_VERB, $url, $params);
        
    }

    public static function post($url, $params = array()) {

        return self::__request(Request::POST_VERB, $url, $params);
        
    }

    public static function download($path, $url) {

        $content = self::__request(Request::GET_VERB, $url, array(), false);

        $file = fopen($path, 'w');
        fwrite($file, $content);
        fclose($file);
        
    }

}