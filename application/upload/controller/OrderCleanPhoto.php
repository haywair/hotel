<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22 0022
 * Time: 14:44
 */

namespace app\upload\controller;

use app\common\base\BnbImage;
use app\upload\base\upload;
class OrderCleanPhoto extends upload
{
    public function index()
    {

        $config = $this->getTypeByString("upload_clean_photo");
        if ($config) {

            $bnbimage = new BnbImage();
            if ($bnbimage->loadImage("file", "upload")) {

                $image = $bnbimage->save("upload_clean_photo");
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