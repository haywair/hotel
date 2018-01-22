<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 13:12
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use app\admin\validate\BnbSpecialprice as Bsv;
use app\admin\model\BnbSpecialprice as Bsmodel;
use think\Request;
class BnbSpecialprice extends Backend
{
    /**
     * BnbSpecialprice模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Bsmodel();
    }

    public function setprice($bid=null){
        $data = $this->model->getBnbSpePriceBid($bid);
        $this->assign('bid',$bid);
        $this->view->assign('data',$data);
        return $this->fetch();
    }
    /**
     * 删除特殊价格
     */
    public function delete($id){
        if($id){
            $result = $this->model->where(['id'=>$id])->update(['status'=>config('state.state_delete')]);
            if($result){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('传入参数不完整');
        }
    }
    /**
     * 增加活动价格
     */
    public function add($bid=null){
        if($this->request->isPost()){
            $param = $this->request->post();
            if($param['price_type'] == 1) {
                $param['end_date'] = $param['end_date'] ? $param['end_date'] : $param['begin_date'];
            }else{
                $param['end_date'] = $param['begin_date'];
            }
            $result = $this->model->save($param);
            if($result){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
        $this->assign('bid',$bid);
        return $this->fetch('addOrEdit');
    }
    /**
     * 修改活动价格
     */
    public function edit($ids=null){
        if($this->request->isPost()){
            $param = $this->request->post();
            if($param['price_type'] == 1) {
                $param['end_date'] = $param['end_date'] ? $param['end_date'] : $param['begin_date'];
            }else{
                $param['end_date'] = $param['begin_date'];
            }
            $result = $this->model->where(['id'=>$param['id']])->update($param);
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else {
            if (!empty($ids)) {
                $act = 'edit';
                $data = $this->model->get($ids);
                $this->assign('bid',$data['bnb_id']);
                $this->assign('row', $data);
                return $this->fetch('addOrEdit');
            } else {
                $this->error('请选择需要编辑的活动');
            }
        }
    }
}