<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/24
 */

namespace app\common\controller;

use app\common\base\WxUser;
use app\common\model\Area;
use app\common\model\Userinfo;
use app\common\model\Users;
use app\common\model\Message;
use think\Request;

class BnbBase extends BnbTheme
{

    private static $ip2region;


    public function _initialize()
    {
        parent::_initialize();

        $this->checkUserInfo();

        self::$ip2region = new \Ip2Region();

        // 已经加载用户记录到session中
        $this->checkUserStatus();
        $this->checkUserMap();

        $this->getUserCity();

        $messageNum = $this->getUserLastMessageTime();
        $this->assign('messageNum',$messageNum);
    }


    /**
     * 根据微信openid自动登录
     */
    private function checkUserInfo()
    {
        (new WxUser($this->getWeChatApp()))->checkWxInfo();
    }

    /**
     * 检查用户状态
     */
    private function checkUserStatus()
    {
        $user = (new Users())->getUserByOpenId($this->getWeChatID());
        if ((!$user) || ($user['status'] <= 0)) {

            $this->redirect("Error/userbanned");
        }
    }

    /*
     * 获取用户地理位置
     */
    private function checkUserMap()
    {
        if (config('setting.Wechat_user_location')) {

            $usermaptime = session(config('session.MapUpdateTime'));

            if (!$usermaptime) {

                $ui = (new Userinfo())->getUserInfo($this->getUserID());
                if ($ui) {
                    $usermaptime = $ui['mapupdate_time'];
                } else {
                    $usermaptime = 0;
                }

                session(config('session.MapUpdateTime'), $usermaptime);
            }

            $interval = config('setting.location')["update_interval"];
            if (($usermaptime + $interval) <= time()) {
                // 获取用户地理位置
                $this->setRefreshUserLocation(true);
            }
        }
    }

    protected function getUserID()
    {
        $s = session(config('session.UserInfo'));
        if (isset($s['id'])) {
            return $s['id'];
        } else {
            $this->redirect("index/Error/userbanned");
            exit();
        }
    }

    protected function getWeChatID()
    {

        $s = session(config('session.UserInfo'));
        if (isset($s['wx_openid'])) {
            return $s['wx_openid'];
        } else {
            $this->redirect("index/Error/userbanned");
            exit();
        }
    }

    private function setRefreshUserLocation($value = true)
    {
        $this->setWechatJsSdkApi('getLocation', $value);
    }

    private function getUserCity()
    {

        $citycode = Request::instance()->get('citycode');
        if ($citycode) {
            $city = (new Area())->getAreaByCityCode($citycode);
            if ($city) {
                session(config('session.CityCode'), $city['code']);
                session(config('session.CityName'), $city['city']);
            } else {
                session(config('session.CityCode'), null);
                session(config('session.CityName'), null);
            }
        }

        if ((!(session(config('session.CityCode')))) || (!(session(config('session.CityName'))))) {

            $city = [];

            $ipaddress = Request::instance()->ip();
            $cityname = self::$ip2region->btreeSearch($ipaddress)['region'];

            if ($cityname) {
                $c = explode("|", $cityname);
                $city = (new Area())->getCityByName($c[3]);
            } else {
                $city = (new Area())->getAreaByCityCode(config('setting.default_location_code'));
            }
            session(config('session.CityCode'), $city['code']);
            session(config('session.CityName'), $city['city']);
        }

        $this->setPageVar('citycode', session(config('session.CityCode')));
        $this->setPageVar('cityname', session(config('session.CityName')));

    }

    /*
     *  接收用户地理位置坐标
     */
    public function setUserLocation()
    {
        $req = Request::instance();
        $lng = $req->post('lng', 0);
        $lat = $req->post('lat', 0);
        (new Userinfo())->updateUserLocation($this->getUserID(), $lng, $lat);
    }
    protected function getUserLastMessageTime(){
        $s = session(config('session.UserInfo'));
        if (isset($s['id'])) {
            $userCondition = [0,$s['id']];
            $lastmessageTime = (new Userinfo())->getUserInfo($s['id']);
            $messageNum = (new Message())->where('to_userid','in',$userCondition)->where('createtime','>=',
                    $lastmessageTime['lastmessage_time'])->count();
            return $messageNum;
        } else {
            $this->redirect("index/Error/userbanned");
            exit();
        }
    }
}