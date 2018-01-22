<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/5 0005
 * Time: 17:21
 */

namespace app\common\model;

use think\Model;
class Questionary extends Model
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

    public function getQuestionList(){
        return $this->where('status','=',config('state.state_ok'))->order('weigh desc,id desc')->select();
    }

    public function getQuestionByID($id){
        return $this->where('id','=',$id)->find();
    }

    public function getQuestionary(){
        $data = $this->where('status',config('state.state_ok'))->order('weigh asc,id asc')->select();
        if($data){
            foreach($data as $k => $v){
                $questionContent = (new QuestionaryContent())->getQuestionListByQID($v['id']);
                $data[$k]['content'] = $questionContent;
                $data[$k]['score']   = $questionContent?$questionContent[0]['score']:0;
            }
        }
        return $data;
    }
}