<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/27
 */

namespace app\index\controller;

use app\common\base\AutoCleaner;
use app\common\base\BnbClean;
use app\common\base\BnbImage;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\CleanOrder;
use app\common\base\LockPoster;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\base\WxMessage;
use app\common\model\Banner;
use app\common\model\Bnb;
use app\common\controller\BnbBase;
use app\common\model\BnbInfo;
use app\common\model\OrderBnb;
use think\Request;
use think\Session;
use app\common\model\Area;
use app\common\model\OrderCleanPhoto;
use Overtrue\Pinyin\Pinyin;


class Testbnb extends BnbBase
{

    public function getlocktoken()
    {
        (new LockPoster())->fetch_passwords("hometest001" , "f497c3b343b4fcebb0c782f6523a4a5c");
    }


    public function testsearch()
    {
        $citycode = "370100";
        $keywords = "";
        $begin_date = "2017-11-26";
        $end_date = "2017-11-27";
        $price_min = 10;
        $price_max = 500;
        $page = 1;
        $prepagenums = 2;

        $r = (new Bnb())->getBnbList($citycode, $keywords, $begin_date, $end_date, $price_min, $price_max, $page, $prepagenums);
        dump($r);
    }

    public function test()
    {
        $bnb_id = 3;

        $start_date = "2017-10-8";
        $end_date = "2017-11-31";

        $now_date = '2017-11-2';

        $p = (new BnbPrice())->getBnbPriceList($bnb_id, $start_date, $end_date, $now_date);
        //dump($p);

        $p = (new BnbPrice())->getBnbJsDateList($bnb_id, $start_date, $end_date, $now_date);
        echo($p);

        die();
    }

    public function testorder()
    {

        $userid = 1000011;
        $bnb_id = 3;

        $begin_date = "2017-11-20";
        $end_date = "2017-11-23";
        $people_numbers = 2;
        $contact_name = "联系人姓名";
        $contact_mobile = "13012345678";
        $contact_content = "";
        $user_voucher_id = 0;
        $clean_numbers = 0;


        $orderLogic = new BnbOrder();

        //$e = $orderLogic->getOrderData($userid, $bnb_id, $begin_date, $end_date, $people_numbers, $contact_name, $contact_mobile, $contact_content, $user_voucher_id, $clean_numbers);
        //dump($e);

        $e = $orderLogic->createBnbOrder($userid, $bnb_id, $begin_date, $end_date, $people_numbers, $contact_name, $contact_mobile, $contact_content, $user_voucher_id, $clean_numbers);
        dump($e);
    }

    public function testcleanorder()
    {
        $userid = 1000011;
        $userid = 0;
        $order_sn = "B20171110164159110100001000011000000349970";
        $clean_numbers = 1;

        $data = (new UserCleanOrder())->getCleanOrderData($userid, $order_sn, $clean_numbers);
        dump($data);

        $o = (new UserCleanOrder())->saveCleanOrder($userid, $order_sn, $clean_numbers);
        dump($o);
    }

    public function testpayorder()
    {
        $userid = 1000000;


        $paylist = "B20171220145243370100001000000000000115075";

        $payorder = new BnbPay();

        $p = $payorder->createPayOrder($userid, $paylist);
        dump($p);
    }

    public function testpaidorder()
    {

        $total_fee = "994.80"; // 订单金额，元
        $out_trade_no = "P20171220150135001000000974322"; // pay_sn

        $total_fee = $total_fee * 100;

        $notify = array(
            'appid' => 'wxff43e94670948b83',
            'attach' => 'h',
            'bank_type' => 'SPDB_CREDIT',
            'cash_fee' => '1',
            'fee_type' => 'CNY',
            'is_subscribe' => 'Y',
            'mch_id' => '1227202702',
            'nonce_str' => 'be4e28c15505b3560df7d602de5e8244',
            'openid' => 'oUK6TjoUe1dcYJU7TgPnbH7_3b6I',
            'out_trade_no' => $out_trade_no,
            'result_code' => 'SUCCESS',
            'return_code' => 'SUCCESS',
            'sign' => '9FA818AD994AD02D3F1A69CA040E8C24',
            'time_end' => '20170505175423',
            'total_fee' => $total_fee,
            'trade_type' => 'JSAPI',
            'transaction_id' => '4010042001201705059677914602',
        );


        $e = (new BnbPaid($this->getWeChatApp()))->paid($notify);
        dump($e);

    }


    public function getcleanorder()
    {
        $userid = 1000011;
        $order_sn = "B20171107132145110100001000011000000363115";
        $order_sn = "B20171110164159110100001000011000000349970";

        $clean_time = "2017-11-22";
        $clean_time = "";

        $error = (new BnbClean())->getBnbCleanData($userid, $order_sn, $clean_time);
        dump($error);
    }


    public function createcleanorder()
    {
        $userid = 1000011;
        $order_sn = "B20171110164159110100001000011000000349970";
        $clean_date = "2017-11-22";
        $clean_demo = "保洁备注";

        $error = (new BnbClean())->createBnbCleanOrder($userid, $order_sn, $clean_date, $clean_demo);
        dump($error);
    }

    public function autocleaner()
    {
        $clean_date = "2017-11-22";
        $dist = (new AutoCleaner())->getCleaner("117", "36", $clean_date);
        dump($dist);

    }


    public function cronAllocCleaner()
    {
        (new CleanOrder())->allocWaitingCleanerOrder();
    }


    public function updateAreaLetter()
    {
        //(new Area())->updateCityLetter();
    }

    public function testpy()
    {
        //(new Area())->updateCityPinYin();
    }


    public function adminBnbCleanPhoto()
    {
        $uploadfile = "E:/temp/IMG_20171114_104114.jpg";
        $bnb_id = 3;
        $name = "测试";
        $id = 1;

        $bi = new BnbImage();
        $bi->loadImage($uploadfile);
        $bi->save('bnb_clean_photo');
    }


    public function uploadBnbCleanPhoto()
    {
        $uploadfile = "E:/temp/IMG_20171114_115723.jpg";
        $clean_order_id = 18;
        $bnb_id = 3;
        $name = "测试";
        $id = 4;

        $bi = new BnbImage();
        $bi->loadImage($uploadfile);
        $x = $bi->save('upload_clean_photo');

        if ($x) {
            $d = (new CleanOrder())->compareCleanPhoto($id, $clean_order_id, $x['file']);
            dump($d);
            if ($d) {
                (new OrderCleanPhoto())->updateCleanPhoto($id, $x['file'], $d);
            }
        }
    }

    public function makeOrderCleanFinish()
    {
        $order_clean_id = 18;
        $cleaner_id = 1000009;
        $e = (new CleanOrder())->makeCleanOrderFinish($order_clean_id, $cleaner_id);
        dump($e);
    }


    public function testaddBnbOrderNumbers()
    {
        $r = (new BnbInfo())->addBnbOrderNumbers(7);
        dump($r);
    }

    public function testUserorders()
    {
        $userid = 1000011;

        $r = (new \app\common\model\OrderBnb())->getOrderListByUserId($userid);
        dump($r);
    }


    public function msg()
    {
        $order_id = 11;

        $orderdata = (new OrderBnb())->getOrderById($order_id);

        $m = (new WxMessage())->orderpaid($orderdata);

        dump($m);
    }
}