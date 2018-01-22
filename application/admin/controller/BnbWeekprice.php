<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 10:19
 */

namespace app\admin\controller;

use app\common\controller\Backend;

use think\Controller;
use app\admin\validate\BnbWeekprice as Bwv;
use app\admin\model\BnbWeekprice as Bmodel;
use think\Request;
class BnbWeekprice extends Backend
{
    /**
     * Bnb模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Bmodel();
    }

    /**
     * @param null $bid房间id
     * 设定房间周价格
     */
    public function add($bid= null){
        $data = $this->model->where(['bnb_id'=>$bid])->select();
        if ($this->request->isPost()) {
            $params = $this->request->post();
            if ($params) {
                try {
                    //是否采用模型验证
                    $validate = new Bwv();
                    if(!$validate->check($params)){
                        $this->error($validate->getError());
                    }
                    if(!$data){
                        $result = $this->model->save($params);
                    }else{
                        $result = $this->model->where(['id'=>$data[0]['id']])->update($params);
                    }

                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error($this->model->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->assign('bnb_id',$bid);
        if(isset($data[0])){
            $this->assign('row',$data[0]);
        }
        return $this->fetch();

    }


}