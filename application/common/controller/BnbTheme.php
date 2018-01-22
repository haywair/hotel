<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/1
 */

namespace app\common\controller;

use app\common\base\WechatEmu;
use think\Controller;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as WxConfigService;

class BnbTheme extends Controller
{

    private $wxapp;

    private $pagevar = [];

    private $wechat_jssdk = null;
    private $wechat_jssdk_api = [];


    public function _initialize()
    {
        parent::_initialize();
        $this->setEnv();
        $this->wxapp = new Application(WxConfigService::load());

    }

    /*
     *  获取微信控制器
     */
    public function getWeChatApp()
    {
        return $this->wxapp;
    }

    /*
    * 设置环境变量
    */
    private function setEnv()
    {
        $this->viewpath = config("env.INDEX_VIEW_PATH") . "/";
        $this->view->config("view_path", $this->viewpath);
        config('dispatch_success_tmpl', config('env.PAGE_ALERT'));
        config('dispatch_error_tmpl', config('env.PAGE_ALERT'));
    }

    public function getPageVar()
    {
        return $this->pagevar;
    }

    public function setPageVar($name, $value)
    {
        $this->pagevar[$name] = $value;
    }

    public function setWechatJsSdk($jssdk)
    {
        $this->wechat_jssdk = $jssdk;
    }

    public function setWechatJsSdkApi($key, $value = true)
    {
        if ($value) {
            $this->wechat_jssdk_api[$key] = true;
            if (!($this->wechat_jssdk)) {
                $this->setWechatJsSdk($this->getWeChatApp()->js);
            }
        } else {
            if (isset($this->wechat_jssdk_api[$key])) {
                unset($this->wechat_jssdk_api[$key]);
            }

            $apilist = $this->getWechatJsSdkApiList();
            if (!$apilist) {
                if ($this->wechat_jssdk) {
                    $this->setWechatJsSdk(null);
                }
            }
        }
    }

    public function getWechatJsSdkApiList()
    {
        return array_keys($this->wechat_jssdk_api);
    }

    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $this->assign('pagevar', $this->getPageVar());
        $this->assign('wechat_emu', (new WechatEmu())->isWechatEmu());
        $this->assign('wechat_jssdk', $this->wechat_jssdk);
        $this->assign('wechat_jssdk_api', $this->getWechatJsSdkApiList());
        return parent::fetch($template, $vars, $replace, $config);
    }

}