<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/28 0028
 * Time: 13:32
 */

namespace app\admin\controller\general;
use app\common\controller\Backend;

use think\Controller;
use think\Request;
use app\common\model\Questionary as Question;
use app\common\model\QuestionaryContent;
class Questionary extends Backend
{

    /**
     * ImageClass模型对象
     */
    protected $model = null;
    protected $contentModel = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Question();
        $this->contentModel = new QuestionaryContent();
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where(['status'=>['IN',[config('state.state_ok'),config('state.state_disable')]]])
                ->where($where)
                ->order('id', 'asc')
                ->count();

            $list = $this->model
                ->where(['status'=>['IN',[config('state.state_ok'),config('state.state_disable')]]])
                ->where($where)
                ->order('id', 'asc')
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
                $count = $this->model->where('id','in',$ids)->update(['status'=>-1]);
            }
            if ($count)
            {
                $this->success();
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 详情
     * @param null $ids
     * @return mixed
     */
    public function more($ids=null){
        if($ids){
            $questionData = $this->model->getQuestionByID($ids);
            $questionContents = $this->contentModel->getQuestionListByQID($ids);
            $list = ['row'=>$questionData,'questionContents'=>$questionContents];
            $this->assign($list);
            return $this->fetch();
        }else{
            $this->error('问卷选项id不能为空');
        }
    }

}
