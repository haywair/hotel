<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/25 0025
 * Time: 17:08
 */

namespace app\admin\controller;


use app\common\controller\Backend;
use app\common\model\Message as Ms;
use think\Controller;
use think\Request;
use app\common\model\Signup;
class Message extends Backend
{
    /**
     * messageType模型对象
     */
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Ms();
    }
    /**
     * 消息列表
     */
    public function index()
    {

        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加查询条件
            $filterData = [];
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname as toName,c.name,d.user_nickname as fromName')
                ->join('__USERS__ b','a.to_userid = b.id','left')
                ->join('__MESSAGE_TYPE__ c','a.message_type_id = c.id','left')
                ->join('__USERS__ d','a.from_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname as toName,c.name,d.user_nickname as fromName')
                ->join('__USERS__ b','a.to_userid = b.id','left')
                ->join('__MESSAGE_TYPE__ c','a.message_type_id = c.id','left')
                ->join('__USERS__ d','a.from_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch('index');
    }
    public function more(){
        $ids = Request::instance()->param('ids');
        if(!$ids){
            $this->error('异常请求');
        }
        $row = $this->model->getMessageInfoById($ids);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}