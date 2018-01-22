<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/25 0025
 * Time: 16:43
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\MessageType as MT;
use think\Controller;
use think\Request;
class MessageType extends Backend
{
    /**
     * messageType模型对象
     */
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MT();
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