<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/17 0017
 * Time: 9:45
 */

namespace app\index\controller;

use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\controller\BnbBase;
use think\Request;
class Message extends BnbBase
{
    public function _initialize()
    {

        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->userinfoModel = new \app\common\model\Userinfo();
        $this->messageModel = new \app\common\model\Message();
        $this->messageTypeModel = new \app\common\model\MessageType();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/'
        ];
    }

    /**
     * 消息列表
     */
    public function index(){
        $params = Request::instance()->param();
        //用户最后阅读消息时间
        $users = $this->userinfoModel->getUserInfo($this->users['id']);
        //更新最后阅读消息时间
        $this->userinfoModel->where('users_id',$this->users['id'])->update(['lastmessage_time'=>time()]);
        $state = [config('state.state_ok')];
        $typeData = $this->messageTypeModel->where('status',config('state.state_ok'))->column('name','id');

        //未读消息
        $noreadMessages = $this->messageModel->getMessageListUser($this->users['id'],$state,$users['lastmessage_time'],'>');
        if($noreadMessages) {
            foreach ($noreadMessages as $kr => $vr) {
                $noreadMessages[$kr]['type'] = $typeData[$vr['message_type_id']];
            }
        }

        //已读消息
        $list = [];
        $page = $params['page'] ?? 1;
        if ($page == 1) {
            $readMessages = $this->messageModel->getMessageListUser($this->users['id'],$state,$users['lastmessage_time'],'<',config('page.order_list_page'),$page);
            $list = $this->getMessageData($typeData,$readMessages);
            $data['history'] = $this->fetch('message/sub/history', $list);
            $pagedata['history'] =  $list['page'];
            $this->assign('data', $data);
            $this->assign('pagedata', $pagedata);

        } else {
            $readMessages = $this->messageModel->getMessageListUser($this->users['id'],$state,$users['lastmessage_time'],'<', config('page.order_list_page'),$page);
            $list = $this->getMessageData($typeData,$readMessages);
            $data['data'] = $this->fetch('message/sub/history', $list);
            $data['page'] = $list['page'];
            return json($data);
        }

        $this->assign('typeData',$typeData);
        $this->assign('noreadMessages',$noreadMessages);
        $this->assign('title','我的消息');
        return $this->fetch();
    }
    /**
     * 消息详情
     */
    public function info($id=null){
        /*if($id){*/
            $this->assign('title','消息详情');
            return $this->fetch();
       /* }*/
    }

    private function getMessageData($typeData,$messageData){
        $page = [];
        if($messageData){
            foreach ($messageData as $kt => $vt) {
                $messageData[$kt]['type'] = $typeData[$vt['message_type_id']];
            }
            $page['page'] = $messageData->currentPage();
            $page['next'] = 0;
            if ($messageData->lastPage() > $messageData->currentPage()) {
                $page['next'] = 1;
            }
        }
        return ['readMessages'=>$messageData,'page'=>$page];


    }
}