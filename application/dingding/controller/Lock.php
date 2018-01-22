<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2018/1/20
 */

namespace app\dingding\controller;

use app\common\model\BnbLockEvent;
use think\Controller;
use think\Request;

class Lock extends Controller
{
    public function index()
    {
        $data = Request::instance()->getInput();

        dump($data);

        (new BnbLockEvent())->addEvent($data);
    }
}