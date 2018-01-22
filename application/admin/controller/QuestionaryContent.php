<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6 0006
 * Time: 8:52
 */

namespace app\admin\controller;
use app\common\controller\Backend;
use think\Controller;
use think\Request;

use think\Db;
use app\common\model\QuestionaryContent as QC;

class QuestionaryContent extends Backend
{
    protected $model = null;
    protected $usersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new QC;
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    //添加
    public function add(){
        if($this->request->isPost()){
            $params = $this->request->post('row/a');
            if($params){
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
            $this->error('参数内容不能为空');

        }else {
            $questionaryId = Request::instance()->param('questionaryId');
            if (!$questionaryId) {
                $this->error('未选择标题内容');
            }
            $this->assign('questionaryId', $questionaryId);
            return $this->fetch();
        }
    }
    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds))
            {
                $count = $this->model->where($this->dataLimitField, 'in', $adminIds)->where('id','in',$ids)->update(['status'=>-1]);
            }
            else
            {
                $count = $this->model->where('id','in',$ids)->delete();
            }
            if ($count)
            {
                $this->success();
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }
}