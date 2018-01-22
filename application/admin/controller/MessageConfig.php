<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/25 0025
 * Time: 16:43
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\MessageConfig as MC;
use app\common\model\MessageType;
use think\Controller;
use think\Request;
class MessageConfig extends Backend
{
    /**
     * messageType模型对象
     */
    protected $model = null;
    protected $messageType;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MC();
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->messageType = $messageType = (new MessageType())->where('status',config('state.state_ok'))->column
        ('name','id');
        $configs = $this->model->where('status',config('state.state_ok'))->select();
        foreach($configs as $k=>$v){
            if(in_array($v['message_type_id'],array_keys($messageType))){
                unset($messageType[$v['message_type_id']]);
            }
        }
        $this->assign('typeAdd',$messageType);
        $this->assign('messageType',$this->messageType);
    }
    /**
     * 消息类型列表
     */
    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('',true);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.name')
                ->join('__MESSAGE_TYPE__ b','a.message_type_id = b.id','left')
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.name')
                ->join('__MESSAGE_TYPE__ b','a.message_type_id = b.id','left')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->assign('messageType',json_encode($this->messageType));
        return $this->view->fetch();
    }
}