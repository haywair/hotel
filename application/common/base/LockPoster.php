<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2018/1/20
 */

namespace app\common\base;

use app\common\model\BnbLockToken;
use GuzzleHttp\Client;

class LockPoster
{

    private $url;
    private $client;
    private $client_key;
    private $token;

    private $https;

    public function __construct()
    {
        $lockconfig = config('lock');

        $this->url = $lockconfig['url'] . $lockconfig['api'];
        $this->client = $lockconfig['client_id'];
        $this->client_key = $lockconfig['client_secret'];

        $this->https = new Client();
        $this->token = $this->getToken();

    }


    public function showToken()
    {
        echo $this->token;
    }

    public function initRoom($home_id, $uuid)
    {
        $action = "update_home";
        $post = [];
        $post['home_id'] = $home_id;
        $post['sp_state'] = 2;

        $r = $this->getRequest($action, $post);
        if ($r && ($r->ErrNo == 0)) {
            return true;
        } else {
            return false;
        }
    }


    public function fetch_passwords($home_id, $uuid)
    {
        $action = "fetch_passwords";

        //$action = 'get_default_password_plaintext';

        $action = "get_center_info";

        //$action = "find_home_device";

        $post = [];
        $post['home_id'] = $home_id;
        $post['uuid'] = $uuid;

        $r = $this->getRequest($action, $post);
        dump($r);

    }


    private function getToken()
    {
        $action = 'access_token';

        $locktoken_model = new BnbLockToken();

        $now = time();

        $token = $locktoken_model->getToken();
        if (config('lock.always_refresh_token') || (!($token['expiredtime'])) || ($token['expiredtime'] - config('lock.token_refresh_time') < $now)) {
            // 需要刷新

            $post = [];
            $post['client_id'] = $this->client;
            $post['client_secret'] = $this->client_key;
            $r = $this->getRequest($action, $post, false);
            if ($r && ($r->ErrNo == 0) && ($r->access_token != "") && ($r->expires_time > $now)) {
                // 获取token成功
                $locktoken_model->setToken($r->access_token, $r->expires_time);
                return $r->access_token;
            } else {
                return '';
            }
        } else {
            return $token['token'];
        }
    }


    private function getRequest($action, $post, $token = true)
    {
        if ($token) {

            if ($this->token != "") {
                $post['access_token'] = $this->token;
            } else {
                return null;
            }
        }

        $post['sign'] = $this->sign($post);
        $res = $this->https->request('POST', $this->url . $action, [
            'json' => $post,
        ]);
        if ($res->getStatusCode() == 200) {
            return \GuzzleHttp\json_decode($res->getBody());
        } else {
            return null;
        }
    }

    private function sign($attributes)
    {
        ksort($attributes);
        return strtolower(md5(urldecode(http_build_query($attributes))));
    }

}