<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/1
 */

namespace app\common\base;

class WechatEmu
{

    /**
     * 是否 微信模拟测试
     * @return bool
     */
    public function isWechatEmu()
    {
        if ((config('test.wechat_emu')) && (!($this->isMicroMessager()))) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 是否微信浏览器
     */
    public function isMicroMessager()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $pos = strpos($user_agent, "MicroMessenger");

        if ($pos !== false) {
            return true;
        } else {
            return false;
        }
    }

}