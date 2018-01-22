<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/6
 */

namespace app\upload\controller;

use app\common\base\BnbImage;
use app\upload\base\upload;

class Evaluate extends upload
{
    public function index()
    {

        $config = $this->getTypeByString("evaluate");
        if ($config) {

            $bnbimage = new BnbImage();
            if ($bnbimage->loadImage("file", "upload")) {

                $image = $bnbimage->save("evaluate");
                //dump($image);
                $this->error->setOk($image);

            } else {
                $this->error->setError(500, '图像文件加载不正确');
            }

        } else {
            $this->error->setError(500, '未找到此文件上传类型');
        }

        return $this->returnJson();
    }
}