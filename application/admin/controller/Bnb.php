<?php

namespace app\admin\controller;

use app\common\controller\Backend;

use app\common\model\BnbInfo;
use app\common\model\Bnb as Bnbcommon;
use think\Controller;
use think\Request;
use app\admin\model\Admin;
use app\admin\model\Users;
use app\admin\model\Area;
use app\admin\model\BnbSpecialprice;
use app\admin\model\Images;
use app\admin\validate\Bnb as Bvalidate;

/**
 * 民宿
 *
 * @icon fa fa-circle-o
 */
class Bnb extends Backend
{

    /**
     * Bnb模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Bnb');
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个方法
     * 因此在当前控制器中可不用编写增删改查的代码,如果需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function store(){
        $state = 0;
        $this->assign('state',$state);
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index();
            return $json;
        }
        return $this->view->fetch('index');
    }
    public function sale(){
        $state = 1;
        $this->assign('state',$state);
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $json = $this->index();
            return $json;
        }
        return $this->view->fetch('index');
    }
    public function index()
    {

        //如果发送的来源是Selectpage，则转发到Selectpage
        if ($this->request->request('pkey_name')) {
            return $this->selectpage();
        }

        //增加查询条件
        $filterData = [];
        $adminUser = session(config('session.Admin'));
        $state = Request::instance()->param('state');
        if(!$adminUser['is_bnb_viewall']){
            $filterData['manager_user'] = [
                'key' => '=',
                'value' => $adminUser['id']
            ];
        }
        if($state == 1){
            $filterData['a.status'] = [
                'key' => 'IN',
                'value' => config('state.state_ok').','.config('state.state_mark')
            ];
        }else{
            $filterData['a.status'] = [
                'key' => '=',
                'value' => config('state.state_disable')
            ];
        }

        list($where,$sort, $order, $offset, $limit) = $this->buildparams(null,true,$filterData);

        $total = $this->model
            ->alias('a')
            ->field('a.*,b.user_nickname as landlord_name,c.username as manage_name,d.province_name,e.city_name')
            ->join('__USERS__ b','a.landlord_user = b.id','left')
            ->join('__ADMIN__ c','a.manager_user = c.id','left')
            ->join('__AREA__ d','a.area_province_code = d.id','left')
            ->join('__AREA__ e','a.area_city_code = e.id','left')
            ->where($where)
            ->order($sort,$order)
            ->count();

        $list = $this->model
            ->alias('a')
            ->field('a.*,b.user_nickname as landlord_name,c.username as manage_name,d.province_name,e.city_name')
            ->join('__USERS__ b','a.landlord_user = b.id','left')
            ->join('__ADMIN__ c','a.manager_user = c.id','left')
            ->join('__AREA__ d','a.area_province_code = d.id','left')
            ->join('__AREA__ e','a.area_city_code = e.id','left')
            ->where($where)
            ->order($sort,$order)
            ->limit($offset, $limit)
            ->select();

        // 设施
        $features_list = \app\admin\model\Features::all();

        $flist = [];
        if (($features_list) && (is_array($features_list))) {
            foreach ($features_list as $v) {
                $flist[$v['id']] = $v['name'];
            }
        }


        if (($list) && (is_array($list))) {
            foreach ($list as $k => $v) {

                $fe = explode(',', $v['features_ids']);
                if (($fe) && (is_array($fe))) {

                    $list[$k]['features_ids'] = "";
                    foreach ($fe as $e) {
                        $list[$k]['features_ids'] .= $flist[$e] . ",";
                    }
                }
                $list[$k]['bnb_image'] = config('upload.upload')['thumb']['thumb2']['dir'].'/'.$v['bnb_image'];

            }
        }
        $provinces = (new Area())-> getProvinceList();
        $result = array("total" => $total, "rows" => $list,'provinces'=>$provinces);
        return json($result);
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
                    $now = time();
                    $params['createtime'] = $now;
                    $params['updatetime'] = $now;
                    $result = $this->model->insertGetId($params);
                    if ($result)
                    {
                        $dataInfo = ['bnb_id'=>$result];
                        (new BnbInfo())->save($dataInfo);

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
        $this->commonData();
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
                    $params['status'] = ($row['status'] == '1'?$row['status']:'0');
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
        $row['bnb_image'] = '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/'.$row['bnb_image'];
        $this->commonData($row['area_province_code']);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * @param null $ids
     * @return string
     * 详情
     */
    public function more($ids=NULL){
        $row = $this->model->getBnbInfo($ids);
        $specialPrice_datas = (new BnbSpecialprice())->getBnbSpePriceBid($ids);
        $images = (new Images())->getBnbImages($ids);
        $imagePath = config('upload.upload');
        $this->assign('images',$images);
        $this->assign('imagePath',$imagePath['thumb']['thumb2']['dir'].'/');
        $this->assign('specialPrice',$specialPrice_datas);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 修改房间信息
     */
    public function info($ids=NULL,$type=Null){
        $field = '*';
        $template = '';
        switch($type){
            case config('bnb.action_info_type'):
                $field = 'id,name,bnb_adwords,demo_content,live_content,attention_content,traffic_content,weigh,
                traffic_content';
                $template = 'info';
                break;
            case config('bnb.action_district_type'):
                $field = 'id,name,area_province_code,area_city_code,area_address,map_lng,map_lat';
                $template = 'district';
                break;
            case config('bnb.action_living_type'):
                $field = 'id,room_people,room_bedroom,room_livingroom,room_space,room_bed,room_toilet,in_hour,out_hour';
                $template = 'livingtype';
                break;
            case config('bnb.action_features_type'):
                $field = 'id,name,features_ids';
                $template = 'features';
                break;
            case config('bnb.action_image_type'):
                $field = 'id,name,bnb_image';
                $template = 'image';
                break;
            case config('bnb.action_offsale_type'):
                $field = 'id,name,status';
                $template = 'offsale';
                break;
            case config('bnb.action_settleprice_type'):
                $field = 'id,name,fee_landlord,fee_cleaner,fee_deposit,fee_clean';
                $template = 'settleprice';
                break;
            case config('bnb.action_lord_type'):
                $field = 'id,name,landlord_user';
                $template = 'lorder';
                $houseMasters = (new Users())->getUsersIdUsername(['status'=>config('state.state_ok'),'is_landlord'=>1]);
                $this->assign('houseMasters',$houseMasters);
                break;
            case config('bnb.action_manager_type'):
                $field = 'id,name,manager_user';
                $adminUsers = (new Admin())->getUsersIdUsername(['status'=>config('state.state_ok')]);
                $this->assign('adminUsers',$adminUsers);
                $template = 'manager';
                break;
            case config('bnb.action_refundfee_type'):
                $field = "id,name,is_refund_fee_manage";
                $template = 'refundfee';
                break;
        }
        $data = $this->model->getBnbInfo($ids,$field);
        if(isset($data['area_province_code'])){
            $this->commonData($data['area_province_code']);
        }
        $this->saveData($data);
        $this->view->assign('row',$data);
        return $this->fetch($template);
    }
    /**
     * 我的绑定民宿
     */
    public function bindBnb(){
        $ids = Request::instance()->param('ids');
        $name = Request::instance()->param('name');
        $province = Request::instance()->param('province');
        $city = Request::instance()->param('city');
        $condition = [];
        if(!$ids){
            $ids = session(config('session.Admin'))['id'];
        }
        $condition['manager_user'] = $ids;
        if($name){
            $condition['a.name'] = array('LIKE','%'.$name.'%');
            $result['name'] = $name;
        }
        if($province){
            $condition['a.area_province_code'] = $province;
            $result['province'] = $province;
        }
        if($city){
            $condition['a.area_city_code'] =$city;
            $result['city'] = $city;
        }

        //已绑定的民宿列表
        $bnbData = (new Bnbcommon())->getAdminBnbs($condition);
        foreach($bnbData as $k=>$v){
            switch($v['status']){
                case config('state.state_ok'):
                    $bnbData[$k]['status'] = '正常';
                    break;
                case config('state.state_disable'):
                    $bnbData[$k]['status'] = '下架';
                    break;
                case config('state.state_delete'):
                    $bnbData[$k]['status'] = '删除';
                    break;
                case config('state.state_mark'):
                    $bnbData[$k]['status'] = '推荐';
                    break;
            }
            $bnbData[$k]['bnb_image'] = '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/'.$v['bnb_image'];
        }
        $page = $bnbData->render();
        if($this->request->isAjax()){
            $result['page'] = $page;
            $result['rows'] = $bnbData;
            return json($result);
        }
        //省
        $provinces = (new Area())-> getProvinceList();
        $provinceCode = array_keys($provinces)[0];
        //市
        $citys = (new Area())->getCitys($provinceCode);
        $list = ['bnbData'=>$bnbData,'provinces'=>$provinces,'citys'=>$citys,'uid'=>$ids,'page'=>$page];
        $this->assign($list);
        return $this->fetch();
    }
    /**
     * 获取房间省、市、管理员、房东公共信息
     */
    private function commonData($provinceCode=Null){
        //管理人员
        $adminUsers = (new Admin())->getUsersIdUsername(['status'=>1]);

        //房东
        $houseMasters = (new Users())->getUsersIdUsername(['status'=>1,'is_landlord'=>1]);
        //省
        $provinces = (new Area())-> getProvinceList();
        //市
        if(!$provinceCode){
           $provinceCode = array_keys($provinces)[0];
        }
        $citys = (new Area())->getCitys($provinceCode);
        $this->assign('adminUsers',$adminUsers);
        $this->assign('houseMasters',$houseMasters);
        $this->assign('provinces',$provinces);
        $this->assign('citys',$citys);
    }
    /**
     * 推荐、不推荐、下架、上架
     */
    public function mark(){
        $id = $this->request->request('id');
        $act = $this->request->request('act');
        if(!empty($id)){
            $info = $this->model->getBnbInfo($id,'*');
            switch($act){
                case 'mark':
                    $data['status'] = ($info['status'] == config('state.state_ok')?config('state.state_mark'):config('state.state_ok'));
                    break;
                case 'saleOrOff':
                    if($info['status'] == '0'){
                        $validate = new Bvalidate();
                        if(!$validate->check($info)){
                            $this->error($validate->getError());
                        }
                        $bnbPrice = (new \app\common\model\BnbWeekprice())->where('bnb_id',$id)->find();
                        if(!$bnbPrice) {
                            $this->error('该民宿尚未设置房间价格,无法上架');
                        }
                        $data['status'] = config('state.state_ok');

                    }else{
                        $data['status'] = config('state.state_disable');
                    }
            }

            $result = $this->model->where(['id'=>$id])->update($data);
            if($result){
                $this->success('操作成功');
            }else{
                $this->success('操作失败');
            }
        }else{
            $this->error('数据不完整！');
        }
    }

    /**
     * 公共插入方法
     */
    private function saveData($row)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                foreach ($params as $k => &$v) {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->save($params);
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($row->getError());
                    }
                } catch (think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }
}
