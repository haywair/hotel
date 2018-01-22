<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29 0029
 * Time: 11:43
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\Voucher as Vcher;
use app\common\model\OrderBnb;
use app\common\model\OrderClean;
use think\Controller;
use think\Config;
use think\Request;
class Voucher extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Vcher();
        $statusList = config('state.state');
        array_pop($statusList);
        $this->view->assign("statusList",  $statusList);
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
            /*$filterData = [];
            $type = $this->request->request('type');
            if($type){
                $filterData = [
                    'type' => [
                        'key' => '=',
                        'value' => $type
                    ]
                ];
            }*/
            list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $total = $this->model
                ->where($where)
                ->order($sort,$order)
                ->count();

            $list = $this->model
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
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                if ($this->dataLimit)
                {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $params['start_time'] = strtotime($params['start_time']);
                    $params['end_time'] = strtotime($params['end_time']);
                    $result = $this->model->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($this->model->getError());
                    }
                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds))
        {
            if (!in_array($row[$this->dataLimitField], $adminIds))
            {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $params['start_time'] = strtotime($params['start_time']);
                    $params['end_time'] = strtotime($params['end_time']);
                    $result = $row->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($row->getError());
                    }
                }
                catch (think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 详情
     */
    public function more($ids){
        if($ids){
            $data = $this->model->getVoucherInfo($ids);
            if($data){
               //最近使用订单
                $orderData = (new OrderBnb())->getOrderByVoucher($ids,config('voucher.latest_voucher_order_number'));
                $this->assign('orderData',$orderData);
                $this->assign('row',$data);
                return $this->fetch();
            }
            $this->error('未找到优惠券记录');
        }
        $this->error('异常请求');
    }
    /**
     * 优惠券配置
     */
    public function config(){
        //优惠券列表
        if($this->request->isAjax()){
            $params = Request::instance()->param();
            if(!$params['key'] || !$params['val']){
                $this->error('请选择优惠券');
            }
            $replaceArr = [
                $params['key'] => $params['val'],
            ];

            $voucherArr = config('voucher');
            $voucherConfig = array_merge($voucherArr,$replaceArr);
            $txt = '<?php return '.var_export($voucherConfig,true).'; ?>';
            $path = substr($_SERVER['DOCUMENT_ROOT'],0,-6).'/application/extra/voucher.php';
            $myfile = fopen($path, "w") ;
            fwrite($myfile, $txt);
            fclose($myfile);
            $this->success('配置成功');
        }else {
            $sign_voucher = config('voucher.sign_voucher');
            $check_voucher = config('voucher.check_voucher');
            $present_voucher = config('voucher.present_voucher');
            $data = ['sign_voucher'=>$sign_voucher,'check_voucher'=>$check_voucher,'present_voucher'=>$present_voucher];
            $this->assign('data',$data);
            return $this->fetch();
        }
    }
}