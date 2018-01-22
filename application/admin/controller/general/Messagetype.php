<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/25 0025
 * Time: 16:43
 */

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\model\MessageType as MT;
use think\Controller;
use think\Request;
class Messagetype extends Backend
{
    /**
     * messageType模型对象
     */
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MT();
        $this->view->assign("statusList", $this->model->getStatusList());
        $typeData = $typeAdd = config('message.message_type');
        $types = $this->model->where('status',config('state.state_ok'))->select();
        foreach($types as $k=>$v){
            if(in_array($v['type_text'],array_keys($typeAdd))){
                unset($typeAdd[$v['type_text']]);
            }
        }
        $this->assign('typeAdd',$typeAdd);
        $this->assign('typeData',$typeData);
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}