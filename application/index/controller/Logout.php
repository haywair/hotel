<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace  app\index\controller;

use think\Controller;

class Logout extends Controller
{
    public function index()
    {
        session(config('session.UserInfo'), null);
        echo '用户已经成功退出！';
    }
}