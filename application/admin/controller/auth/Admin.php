<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use app\common\model\Bnb;
use app\admin\model\Area;
use think\Request;
use fast\Random;
use fast\Tree;

/**
 * 管理员管理
 *
 * @icon fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Admin extends Backend
{

    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Admin');

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds();

        $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
                ->column('id,name');
        foreach ($groupName as $k => &$v)
        {
            $v = __($v);
        }
        unset($v);

        $this->view->assign('groupdata', $groupName);
        $this->assignconfig("admin", ['id' => $this->auth->id]);
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax())
        {

            $childrenGroupIds = $this->auth->getChildrenAdminIds(true);
            $groupName = AuthGroup::column('id,name');
            $authGroupList = AuthGroupAccess::where('uid', 'in', $childrenGroupIds)
                ->field('uid,group_id')
                ->select();

            $adminGroupName = [];
            foreach ($authGroupList as $k => $v)
            {
                if (isset($groupName[$v['group_id']]))
                    $adminGroupName[$v['uid']][$v['group_id']] = $groupName[$v['group_id']];
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where('id', 'in', $this->childrenAdminIds)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where('id', 'in', $this->childrenAdminIds)
                ->field(['password', 'salt', 'token'], true)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => &$v)
            {
                $groups = isset($adminGroupName[$v['id']]) ? $adminGroupName[$v['id']] : [];
                $v['groups'] = implode(',', array_keys($groups));
                $v['groups_text'] = implode(',', array_values($groups));
            }
            unset($v);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
                $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。

                $admin = $this->model->create($params);
                $group = $this->request->post("group/a");

                //过滤不允许的组别,避免越权
                $group = array_intersect($this->childrenGroupIds, $group);
                $dataset = [];
                foreach ($group as $value)
                {
                    $dataset[] = ['uid' => $admin->id, 'group_id' => $value];
                }
                model('AuthGroupAccess')->saveAll($dataset);
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                if ($params['password'])
                {
                    $params['salt'] = Random::alnum();
                    $params['password'] = md5(md5($params['password']) . $params['salt']);
                }
                else
                {
                    unset($params['password'], $params['salt']);
                }
                $row->save($params);

                // 先移除所有权限
                model('AuthGroupAccess')->where('uid', $row->id)->delete();

                $group = $this->request->post("group/a");

                // 过滤不允许的组别,避免越权
                $group = array_intersect($this->childrenGroupIds, $group);

                $dataset = [];
                foreach ($group as $value)
                {
                    $dataset[] = ['uid' => $row->id, 'group_id' => $value];
                }
                model('AuthGroupAccess')->saveAll($dataset);
                $this->success();
            }
            $this->error();
        }
        $grouplist = $this->auth->getGroups($row['id']);
        $groupids = [];
        foreach ($grouplist as $k => $v)
        {
            $groupids[] = $v['id'];
        }
        $this->view->assign("row", $row);
        $this->view->assign("groupids", $groupids);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            // 避免越权删除管理员
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->model->where('id', 'in', $ids)->where('id', 'in', function($query) use($childrenGroupIds) {
                        $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
                    })->select();
            if ($adminList)
            {
                $deleteIds = [];
                foreach ($adminList as $k => $v)
                {
                    $deleteIds[] = $v->id;
                }
                $deleteIds = array_diff($deleteIds, [$this->auth->id]);
                if ($deleteIds)
                {
                    $this->model->destroy($deleteIds);
                    model('Bnb')->where('manager_user','in',$deleteIds)->update(['manager_user'=>0]);
                    model('AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                    $this->success();
                }
            }
        }
        $this->error();
    }

    /**
     * 批量更新
     * @internal
     */
    public function multi($ids = "")
    {
        // 管理员禁止批量操作
        $this->error();
    }
    /**
     * 绑定民宿
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
        $bnbData = (new Bnb())->getAdminBnbs($condition);
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
     * 添加绑定
     * @return mixed
     */
    public function addBnb(){
        if($this->request->isAjax()){
            $params = $this->request->request('row/a');
            if(!$params['bnb_ids'] || !$params['manager_user']){
                $this->error('未选择民俗或指定管理员');
            }
            $dataBnb = [];
            foreach($params['bnb_ids'] as $v){
                $dataBnb[] = [
                    'id' => $v,
                    'manager_user' =>   $params['manager_user']
                ];
            }
            $res = model('Bnb')->saveAll($dataBnb);
            if(!$res){
                $this->error('绑定民俗失败');
            }
            $this->success('绑定成功');
        }
        $uid = Request::instance()->param('uid');
        if(!$uid){
            $this->error('未选定需要绑定的管理员');
        }
        $provinces = (new Area())-> getProvinceList();
        $provinceCode = array_keys($provinces)[0];
        //市
        $citys = (new Area())->getCitys($provinceCode);
        $list = ['provinces'=>$provinces,'citys'=>$citys,'uid'=>$uid];
        $this->assign($list);
        return $this->fetch();
    }

    /**
     * 获取未绑定民宿
     */
    public function getBnbs(){
        if($this->request->isAjax()){
            $uid = Request::instance()->param('uid');
            $citycode = Request::instance()->param('areacode');

            if(!$uid || !$citycode){
                $this->error('未选择民宿所在的城市或要绑定的管理员');
            }
            $bnbData = (new Bnb())->getUnbindAdminBnbs($citycode);
            if(!$bnbData){
                $this->error('该城市民宿都已绑定了');
            }
            $this->success('获取数据成功','',$bnbData);
        }else{
            $this->error('异常请求');
        }
    }

    /**
     * 移除绑定
     */
    public function removebind(){
        $params = $this->request->request();
        if(!$params['ids'] || !$params['uid']){
            $this->error('未指定需移除绑定的管理员或民宿');
        }

        $bnbIds = model('Bnb')->where('manager_user',$params['uid'])->column('id');
        foreach($params['ids'] as $v){
            if($v && !in_array($v,$bnbIds)){
                $this->error('只能移除该管理员所属的民宿');
            }
        }

        if(is_array($params['ids'])){
            $ids = implode(',',array_filter($params['ids']));
        }else{
            $ids = $params['ids'];
        }
        $res = model('Bnb')->where('id','IN',$ids)->update(['manager_user'=>0]);
        if(!$res){
            $this->error('移除绑定失败');
        }
        $this->success('移除绑定成功');
    }

}
