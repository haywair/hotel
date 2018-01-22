<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/5 0005
 * Time: 18:11
 */

namespace app\common\model;

use think\Model;
class QuestionaryContent extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function getStatusList()
    {
        return ['-1' => '已删除','0' =>'隐藏','1' =>'正常'];
    }

    public function getQuestionListByQID($questionaryId){
        if(!$questionaryId){
            return null;
        }
        $data = $this->where('status','=',config('state.state_ok'))
             -> where('questionary_id','=',$questionaryId)
             -> order('weigh asc,id asc')
             -> select();
        return $data?$data:null;
    }

    public function getContentByID($id){
        return $this->where('id','=',$id)->find();
    }
}