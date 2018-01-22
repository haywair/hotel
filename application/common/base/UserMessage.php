<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18 0018
 * Time: 15:52
 */

namespace app\common\base;

use app\common\model\Bnb;
use app\common\model\OrderBnb;
use app\common\model\OrderUserClean;
use app\common\model\Users;
use think\Exception;
use Carbon\Carbon;
use app\common\model\Message;
use app\common\model\MessageType;
use app\common\model\MessageConfig;
use app\common\model\OrderClean;
class UserMessage
{

    public static $MessageBnb = 'B';//民宿订单消息
    public static $MessageClean = 'C';//保洁订单消息
    public static $MessageQuestionary = 'Q';//保洁订单消息
    /**
     *  自动为即将入住的订单发送消息
     *
     */
    public function WaitCheckBnbOrder()
    {
        $now = time();
        // 根据当前时间，获取不同的订单列表
        $messageTime = config('setting.msg_auto_send_bnb_day');
        $beginTime = date('Y-m-d',$now);
        $endTime = date('Y-m-d',($now + $messageTime*3600*24));
        $messageType = config('message.order_bnb_msg');//民宿订单
        $msgtypeData = (new MessageType())->where('type_text',config('message.order_bnb_msg'))->find();
        $orderList = (new OrderBnb())-> where('in_date','between',[$beginTime,$endTime])->select();
        foreach($orderList as $k=>$v){
            $msgBegin = strtotime($v['in_date'])-3600*24*$messageTime;
            $msgEnd = strtotime($v['in_date']);
            $messageNum = (new Message())->where('order_sn',$v['order_sn'])->where('createtime','between',
                    [$msgBegin,$msgEnd])->where('messsage_type_id',$msgtypeData['id'])->count();
            if($messageNum <= 0){
                $this->sendMessage($v['order_sn'],self::$MessageBnb);
            }
        }
    }
    //自动为即将保洁的房间发送消息
    public function WaitCheckCleanOrder()
    {
        $now = time();
        // 根据当前时间，获取不同的订单列表
        $messageTime = config('setting.msg_auto_send_clean_hour');
        $beginTime = $now;
        $endTime = $now + $messageTime*3600;
        $messageType = config('message.order_clean_msg');//保洁订单
        $msgtypeData = (new MessageType())->where('type_text',config('message.order_bnb_msg'))->find();
        $orderList = (new OrderClean())-> where('clean_start_time','between',[$beginTime,$endTime])->select();
        foreach($orderList as $k=>$v){
            $msgBegin = $v['clean_start_time']-3600*24*$messageTime;
            $msgEnd = $v['clean_start_time'];
            $messageNum = (new Message())->where('order_sn',$v['order_sn'])->where('createtime','between',
                [$msgBegin,$msgEnd])->where('messsage_type_id',$msgtypeData['id'])->count();
            if($messageNum <= 0){
                $this->sendMessage($v['order_sn'],self::$MessageClean);
            }
        }
    }
    //自动为即将入住订单发送调查问卷
    public function WaitCheckQuestionary()
    {
        $now = time();
        $in_date = date('Y-m-d',($now+24*3600));
        // 根据当前时间，获取不同的订单列表
        $messageType = config('message.order_bnb_msg');//民宿订单
        $msgtypeData = (new MessageType())->where('type_text',config('message.order_bnb_msg'))->find();
        $orderList = (new OrderBnb())-> where('in_date',$in_date)->select();
        foreach($orderList as $k=>$v){
            $messageNum = (new Message())->where('order_sn',$v['order_sn'])->where('message_type_id',$msgtypeData['id'])->count();
            if($messageNum <= 0){
                $this->sendMessage($v['order_sn'],self::$MessageQuestionary);
            }
        }
    }

    public function sendMessage($orderSn,$messageType,$payState = true){
        $msgModel = new Message();
        $msgconfigModel = new MessageConfig();
        $msgtypeModel = new MessageType();
        $model = '';
        $template = '';
        $messageData = [];
        switch($messageType){
            case config('message.order_bnb_msg')://民宿订单
                $model = new OrderBnb();
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->orderBnbMessage($orderMsg);
                break;
            case config('message.order_clean_msg')://保洁订单
                $model = new OrderClean();
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->orderCleanMessage($orderMsg);
                break;
            case config('message.order_clean_cancel_msg')://保洁订单取消
                $model = new OrderClean();
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->orderCleanMessage($orderMsg);
                break;
            case config('message.order_clean_change_msg')://保洁订单时间发生变化
                $model = new OrderClean();
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->orderCleanMessage($orderMsg);
                break;
            case config('message.questionary_reply_msg')://调查问卷
                $model = new OrderBnb();
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->questionaryMessage($orderMsg);
                break;
            case config('message.pay_state_msg')://支付结果通知
                $firstSn = substr($orderSn,0,1);
                if($firstSn == 'B'){
                    $model = new OrderBnb();
                }else if($firstSn == 'E'){
                    $model = new OrderClean();
                }
                $orderMsg = $this->getOrderMsg($model,$orderSn);
                $messageData = $this->payMessage($orderMsg,$payState);
                break;
        }
        $msgModel->save($messageData);

    }

    protected function orderBnbMessage($orderMsg){
        $template = $orderMsg['templateData']['content'];
        $template  = str_replace('{fromName}',$orderMsg['landData']['user_nickname'],$template);
        $template  = str_replace('{bnbName}',$orderMsg['bnbData']['name'],$template);
        $template  = str_replace('{userName}',$orderMsg['userData']['user_nickname'],$template);
        $message = [
            'message_type_id'   =>  $orderMsg['msgtypeData']['id'],
            'to_userid'         =>  $orderMsg['orderData']['user_id'],
            'order_sn'          =>  $orderMsg['orderData']['order_sn'],
            'msg_content'       =>  $template,
            'url'               =>  url('index/order/detail',['order_sn'=>$orderMsg['orderData']['order_sn']])
        ];
        return $message;
    }

    protected function orderCleanMessage($orderMsg){
        $cleanerData = (new Users())->getUserById($orderMsg['orderData']['cleaner_id']);
        $template = $orderMsg['templateData']['content'];
        $message = [
            'message_type_id'   =>  $orderMsg['msgtypeData']['id'],
            'to_userid'         =>  $orderMsg['orderData']['user_id'],
            'order_sn'          =>  $orderMsg['orderData']['order_sn'],
            'msg_content'       =>  $template,
            'url'               =>  url('index/order/clean',['order_sn'=>$orderMsg['orderData']['order_sn']])
        ];
        return $message;
    }

    protected function payMessage($orderMsg,$payState){
        $template = $orderMsg['templateData']['content'];
        if(isset($orderMsg['orderData']['cleaner_id'])){
            $cleanerData = (new Users())->getUserById($orderMsg['orderData']['cleaner_id']);
            $url = url('index/order/clean',['order_sn'=>$orderMsg['orderData']['order_sn']]);
        }
        $url = url('index/order/detail',['order_sn'=>$orderMsg['orderData']['order_sn']]);
        $payText = $payState?'支付成功':'支付失败';
        $message = [
            'message_type_id'   =>  $orderMsg['msgtypeData']['id'],
            'to_userid'         =>  $orderMsg['orderData']['user_id'],
            'order_sn'          =>  $orderMsg['orderData']['order_sn'],
            'msg_content'       =>  $template,
            'url'               =>  $url
        ];
        return $message;
    }

    protected function questionaryMessage($orderMsg){
        $template = $orderMsg['templateData']['content'];
        $message = [
            'message_type_id'   =>  $orderMsg['msgtypeData']['id'],
            'to_userid'         =>  $orderMsg['orderData']['user_id'],
            'order_sn'          =>  $orderMsg['orderData']['order_sn'],
            'msg_content'       =>  $template,
            'url'               =>  url('index/order/questionary',['order_sn'=>$orderMsg['orderData']['order_sn']])
        ];
        return $message;
    }
    protected function getOrderMsg($model,$orderSn){
        $orderData = $model->where('order_sn',$orderSn)->find();
        $userData = (new Users())->getUserById($orderData['user_id']);
        $bnbData = (new Bnb())->getBnb($orderData['bnb_id']);
        $landData = (new Users())->getUserById($bnbData['landlord_user']);
        $msgtypeData = (new MessageType())->where('type_text',config('message.order_bnb_msg'))->find();
        $templateData = (new MessageType())->where('message_type_id',$msgtypeData['id'])->find();
        return [
            'orderData' =>  $orderData,
            'userData'  =>  $userData,
            'bnbData'   =>  $bnbData,
            'landData'  =>  $landData,
            'msgtypeData'   =>  $msgtypeData,
            'templateData'  =>  $templateData
        ];
    }

}