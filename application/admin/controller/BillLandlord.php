<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29 0029
 * Time: 9:19
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use think\Request;
use app\common\model\BillLandlord as BL;
class BillLandlord extends Backend
{
    /**
     *模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new BL();
    }
    /**
     * 查看
     */
    public function index(){
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {
                return $this->selectpage();
            }
            //增加查询条件
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            $filterData = $this->getSearchParams($params);
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.name,d.order_sn,e.contact_mobile')
                ->join('__USERS__ b','a.landlord_id = b.id','left')
                ->join('__LANDLORDINFO__ e','a.landlord_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_BNB__ d','a.order_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.name,d.order_sn,e.contact_mobile')
                ->join('__USERS__ b','a.landlord_id = b.id','left')
                ->join('__LANDLORDINFO__ e','a.landlord_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_BNB__ d','a.order_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch('index');
    }

    /**
     * 结算详情
     * @param null $ids
     * @return string
     */
    public function more($ids=NULL){
        if($ids){
            $row = $this->model->getBillById($ids);
            $state = config('state.state');
            $this->view->assign("row", $row);
            $this->assign('stateList',$state);
            return $this->view->fetch();
        }else{
            $this->error('无有效信息');
        }

    }
    private function getSearchParams($params){
        $filterData = [];
        if($params){
            foreach($params as $k=>$v){
                if($v) {
                    switch($k){
                        case 'order_sn':
                            $k = 'd.order_sn';
                            break;
                        case 'name':
                            $k = 'c.name';
                            break;
                        case 'user_nickname':
                            $k = 'b.user_nickname';
                            break;
                        case 'contact_mobile':
                            $k = 'e.contact_mobile';
                            break;
                    }
                    $filterData[$k] =  [
                        'key' => 'LIKE',
                        'value' => $v
                    ];
                }
            }
        }
        return $filterData;
    }
}