<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\index\controller;

use app\common\controller\BnbTheme;

class Error extends BnbTheme
{

    /**
     *  获取用户数据失败，或者用户没有关注公众号
     */
    public function nowechat()
    {
        return $this->fetch();
    }


    public function userbanned()
    {
        return $this->fetch();
    }

    public function index()
    {
        return $this->fetch();
    }

    public function _empty()
    {
        return $this->fetch('index');
    }
}