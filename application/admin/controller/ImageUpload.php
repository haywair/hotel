<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/30
 */

namespace app\admin\controller;

use app\admin\library\Auth;
use app\common\base\BnbImage;
use app\common\base\Error;
use think\Config;
use think\Controller;

class ImageUpload extends Controller
{

    private $error;

    public function __construct()
    {
        $this->error = new Error(['code' => 0]);
        // 判断是否登陆
        if (!((new Auth())->isLogin())) {
            $this->error->setError(400, '请您先登录系统');
            return $this->error->returnJson();
            die();
        }
    }


    public function index($type, $field = "file",$ext=null)
    {

        $config = $this->getTypeByString($type);
        if ($config) {

            $bnbimage = new BnbImage();
            if($ext){
                $ext = explode('|',$ext);
            }
            if ($bnbimage->loadImage($field, "upload")) {
                $image = $bnbimage->save($type,$ext);
                //dump($image);
                $this->error->setOk($image);

            } else {
                $this->error->setError(500, '图像文件加载不正确');
            }

        } else {
            $this->error->setError(500, '未找到此文件上传类型');
        }

        Config::set('default_return_type', 'json');
        if ($this->error->checkResult()) {

            $data = $this->error->getData();

            $this->success("上传成功", null, ['url' => $data['file']]);
        } else {
            $this->error('上传失败');
        }
    }


    private function getTypeByString($filetype)
    {
        $config = config('upload.imagetype');
        if (in_array($filetype, $config)) {
            return config('upload.' . $filetype);
        }
        return null;
    }
}
