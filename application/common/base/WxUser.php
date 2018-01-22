<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/24
 */

namespace app\common\base;


use EasyWeChat\Core\Exception;
use EasyWeChat\Support\Collection;
use think\Request;

class WxUser
{

    private $app;
    private $nowechat;


    public function __construct($app)
    {
        $this->app = $app;
        $this->nowechat = url('index/Error/nowechat', '', true, true);
    }


    /**
     * 检查微信信息
     * @return mixed
     */
    public function checkWxInfo()
    {
        if (!session(config('session.UserInfo'))) {
            // 获取当前页面url
            $nowurl = Request::instance()->url(true);
            session(config('session.BackUrl'), $nowurl);

            if ((new WechatEmu())->isWechatEmu()) {
                $this->getWxUser();
            } else {
                $response = $this->app->oauth->scopes(['snsapi_userinfo'])->redirect();
                return $response->send();
            }
        }
    }


    /**
     * 获取微信用户信息
     */
    public function getWxUser()
    {
        $wx_userinfo = new Collection();

        if ((new WechatEmu())->isWechatEmu()) {
            $wctest = config('test.wechat');
            foreach ($wctest as $k => $v) {
                $wx_userinfo->set($k, $v);
            }

        } else {
            try {
                $user = $this->app->oauth->user();
                if ($user) {
                    $openid = $user->getId();
                    $wx_userinfo = $this->app->user->get($openid);
                }
            } catch (Exception $e) {
                $wx_userinfo = new Collection();
            }
        }

        // 判断是否成功获取用户数据
        if (!$wx_userinfo->openid) {
            $this->gotoURL($this->nowechat, false);
        }

        if (config('setting.Wechat_need_subscribe')) {
            if (!($wx_userinfo->subscribe)) { // 没有订阅公众号
                $this->gotoURL($this->nowechat, false);
            }
        }

        // 设置用户信息
        $bnbuser = new BnbUser();
        $user = $bnbuser->getUserInfo($wx_userinfo);
        session(config('session.UserInfo'), $user);

        $this->gotoURL();
    }

    public function gotoURL($backurl = "/", $session = true)
    {
        if ($session) {
            if (session(config('session.BackUrl'))) {
                $backurl = session(config('session.BackUrl'));
                session(config('session.BackUrl'), null);
            }
        }
        header('location: ' . $backurl);
        exit();
    }
}