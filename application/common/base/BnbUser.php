<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\common\base;

use app\common\model\Userinfo;
use app\common\model\Users;
use think\Request;

class BnbUser
{
    private $usermodel;

    public function __construct()
    {
        $this->usermodel = new Users();
    }

    public function getUserInfo($wx_userinfo)
    {
        $user = $this->usermodel->getUserByOpenId($wx_userinfo->openid);
        if (!$user) {
            $user = $this->autoRegisteNewUser($wx_userinfo);
        }

        // 处理user数据

        $duser = [];
        $duser['id'] = $user['id'];
        $duser['wx_openid'] = $user['wx_openid'];
        $duser['nickname'] = $user['user_nickname'];
        $duser['avatar'] = config('upload.avatar.thumb.avatar.dir') . "/" . $user['user_avatar'];
        $duser['sex'] = $user['user_sex'];

        return $duser;
    }


    public function autoRegisteNewUser($wx_userinfo)
    {
        $user = [];

        $user['wx_openid'] = $wx_userinfo->openid;
        $user['user_nickname'] = $wx_userinfo->nickname;
        $user['user_avatar'] = $wx_userinfo->headimgurl;
        $user['user_sex'] = $wx_userinfo->sex;
        $user['user_class'] = 1;
        $user['user_truename'] = "";
        $user['user_mobile'] = "";
        $user['user_idcard_number'] = "";
        $user['user_idcard_image'] = "";
        $user['is_landlord'] = 0;
        $user['is_cleaner'] = 0;
        $user['createtime'] = time();
        $user['updatetime'] = time();

        $userid = $this->usermodel->insertGetId($user);
        if ($userid) {
            $avatar = new BnbImage();
            $avatar->loadImage($wx_userinfo->headimgurl);
            $avatarfile = $avatar->save('avatar', [$userid]);

            $update_user['user_avatar'] = $avatarfile['file'];
            $update_avatar = $this->usermodel->where('id', $userid)->update($update_user);
            if ($update_avatar) {
                $user['user_avatar'] = $avatarfile['file'];
                $user['id'] = $userid;
            }

            // userinfo
            $ui = [];
            $ui['users_id'] = $userid;
            $ui['map_lng'] = 0;
            $ui['map_lat'] = 0;
            $ui['mapupdate_time'] = 0;
            $ui['lastlogin_time'] = time();
            $ui['lastlogin_ip'] = Request::instance()->ip();
            $ui['login_numbers'] = 1;
            $ui['money'] = 0;
            $ui['lastmessage_time'] = time();

            (new Userinfo())->create($ui);
        }

        return $user;
    }
}