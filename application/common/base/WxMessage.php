<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2018/1/2
 */

namespace app\common\base;
use app\common\model\Bnb;
use app\common\model\OrderBnb;
use app\common\model\Users;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as ConfigService;


class WxMessage
{
    public $notice = null;
    public $template = [];


    public function __construct()
    {
        $app =  new Application(ConfigService::load());
        $this->notice = $app->notice;

        $this->template = config("wxmessage");
    }


    public function orderpaid($orderdata)
    {


        $order  = (new OrderBnb())->getOrderBySn($orderdata['order_sn']);
        if ($order) {
            $bnb = (new Bnb())->getBnb($order['bnb_id']);

            $user = (new Users())->getUserById($order['user_id']);

            if ($bnb && $user) {
                $data = [
                    'first' => '欢迎您入住' . $bnb['name'],
                    'keyword1' => $bnb['name'],
                    'keyword2' => $order['in_date'] . " " . $order['in_hour'] . " - " . $order['out_date'] . " " . $order['out_hour'],
                    'keyword3' => '将在您入住前24小时发送给您',
                    'keyword4' => $bnb['area_address'],
                    'remark' => '更多信息请点击此信息查看入住详情',
                ];

                $url = url('index/Order/detail', ['order_sn' => $order['order_sn']], true, true);

                $messageId = $this->notice->to($user['wx_openid'])->uses($this->template['OrderPaid']['wxmessage'])->andUrl($url)->data($data)->send();
            }
        }
    }

    
}