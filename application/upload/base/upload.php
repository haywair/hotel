<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/6
 */

namespace app\upload\base;

use app\common\base\Error;
use think\Controller;

class upload extends Controller
{

    public $error;

    public function _initialize()
    {
        parent::_initialize();

        $this->error = new Error(['code' => 0]);

        if (!session(config('session.UserInfo'))) {
            die(json_encode(['code' => 300, 'text' => '用户未登录']));
        }
    }

    public function getTypeByString($filetype)
    {
        $config = config('upload.imagetype');
        if (in_array($filetype, $config)) {
            return config('upload.' . $filetype);
        }
        return null;
    }

    public function returnJson()
    {
        $e = $this->error;
        $jse =json_encode($e->returnJson());
        return $jse;
    }
}