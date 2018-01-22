<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/7 0007
 * Time: 14:54
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\ImageList;
use app\common\model\BnbCleanPhoto as BCP;
use think\Request;
use app\common\base\BnbImage;
class BnbCleanPhoto extends Backend
{
    /**
     * Images模型对象
     */
    protected $model = null;
    protected $classModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new BCP();
    }
    /**
     * @param null $bid
     * @return mixed
     * 查看
     */
    public function index($bid=null){
        $condition = [];
        if($this->request->isPost()){
            $params = $this->request->request();
            if(isset($params) && $params['name']){
                $condition['name'] = array('LIKE','%'.$params['name'].'%');
                $this->assign('name',$params['name']);
            }
            if(isset($params) && $params['bnb_id']){
                $bid = $params['bnb_id'];
            }
        }
        if($bid) {
            $condition['bnb_id'] = $bid;
            $condition['status'] = ['in',[config('state.state_ok'),config('state.state_disable')]];
            $data = $this->model->getImagesListByBID($condition);
            $page = $data->render();
            $imagePath = config('upload.bnb_clean_photo');
            $this->assign('data', $data);
            $this->assign('page', $page);
            $this->assign('bid', $bid);
            $this->assign('stateData',config('state.state'));
            $this->assign('imagePath',$imagePath['thumb']['thumb']['dir'].'/');
            return $this->fetch();
        }else{
            $this->error('未传入有效民宿信息');
        }
    }
    /*
     *添加图片
     */
    public function add($bid=null){
        if($this->request->isPost()){
            $post = $this->request->post('row/a');
            $result = $this->model->save($post);
            if($result){
                $this->success('上传成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            if($bid){
                $this->assign('bid',$bid);
                return $this->fetch();
            }else{
                $this->error('未选择民宿');
            }
        }
    }
    /**
     * 编辑图片
     */
    public function edit($id=null){
        if($this->request->isPost()){
            $post = $this->request->post('row/a');
            $result = $this->model->where('id',$post['id'])->update($post);
            if($result){
                $this->success('上传成功');
            }else{
                $this->error('操作失败');
            }
        }else {
            if ($id) {
                $data = $this->model->getImageById($id);
                $this->assign('bid', $data['bnb_id']);
                $this->assign('row', $data);
                return $this->fetch('add');
            } else {
                $this->error('无有效图片参数');
            }
        }
    }
    /**
     * 更新图片状态
     */
    public function updateState(){
        if($this->request->isAjax()){
            $params = $this->request->post();
            $count = $this->model->where('id',$params['id'])->count();
            $data = [];
            if($count > 0){
                $data['status'] = $params['status']?$params['status']:'0';
                $result = $this->model->updateImage($params['id'],$data);
                if($result){
                    $this->success('操作成功');
                }else{
                    $this->error('操作失败');
                }
            }else{
                $this->error('该图片不存在');
            }
        }else{
            $this->error('异常请求');
        }
    }
}