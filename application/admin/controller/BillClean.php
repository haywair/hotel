<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27 0027
 * Time: 9:20
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use think\Request;
use app\common\model\BillClean as Bc;
class BillClean extends Backend
{

    /**
     *模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Bc();
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
            $filter = Request::instance()->request('filter');
            $params = json_decode($filter,true);
            //增加查询条件
            $filterData = $this->getSearchParams($params);

            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);
            $total = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.name,d.order_sn,e.contact_mobile')
                ->join('__USERS__ b','a.cleaner_id = b.id','left')
                ->join('__CLEANINFO__ e','a.cleaner_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_CLEAN__ d','a.clean_order_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
                ->alias('a')
                ->field('a.*,b.user_nickname,c.name,d.order_sn,e.contact_mobile')
                ->join('__USERS__ b','a.cleaner_id = b.id','left')
                ->join('__CLEANINFO__ e','a.cleaner_id = e.users_id','left')
                ->join('__BNB__ c','a.bnb_id = c.id','left')
                ->join('__ORDER_CLEAN__ d','a.clean_order_id = d.id','left')
                ->where($where)
                ->order($sort,$order)
                ->limit($offset, $limit)
            //    ->fetchSql(true)
                ->select();
            //echo $list;die();
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

    /**
     * @param null $cleaner_id
     * @return mixed|\think\response\Json
     */
    public function billCleanPreview($cleaner_id=null){
        if(!$cleaner_id){
            $this->error('未选定保洁员');
        }
        $condition = [
            'a.cleaner_id' => $cleaner_id,
            'a.status' => config('state.state_ok')
        ];
        $params = $this->request->request();
        $result = [];
        if(isset($params['name']) && $params['name']){
            $condition['c.name'] = array('LIKE','%'.$params['name'].'%');
            $result['name'] = $params['name'];
        }
        if(isset($params['order_sn']) && $params['order_sn']){
            $condition['b.order_sn'] = array('LIKE','%'.$params['order_sn'].'%');
            $result['order_sn'] = $params['order_sn'];
        }
        if(isset($params['bill_date']) && $params['bill_date']){
            $condition['a.bill_date'] = $params['bill_date'];
            $result['bill_date'] = $params['bill_date'];
        }

        $data = $this->model-> getListData($condition);
        $page = $data->render();
        if($this->request->isAjax()){
            $result['page'] = $page;
            $result['rows'] = $data;
            return json($result);
        }else{
            $this->assign('uid',$cleaner_id);
            $this->assign('data',$data);
            $this->assign('page',$page);
            return $this->fetch();
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