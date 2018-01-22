<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\common\base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Downloader
{
    public $client = null;

    public function __construct()
    {
        $this->client = new Client();
    }


    public function download($url)
    {
        $body = null;
        $httpclient = $this->getClient();
        try {
            $request = $httpclient->createRequest('GET', $url);
            $response = $httpclient->send($request);
            $body = $response->getBody();
        } catch (RequestException $e) {
            $body = null;
        }

        return $body;
    }
}