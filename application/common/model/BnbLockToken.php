<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2018/1/20
 */

namespace app\common\model;

use think\Model;

class BnbLockToken extends Model
{

    public function getToken()
    {
        return $this->where([])->order('expiredtime desc')->find();
    }

    public function setToken($token, $expiredtime)
    {
        $now = time();

        if (($token) && ($expiredtime > $now)) {
            $data = [];
            $data['createtime'] = time();
            $data['token'] = $token;
            $data['expiredtime'] = $expiredtime;
            return $this->insert($data);
        } else {
            return false;
        }
    }
}