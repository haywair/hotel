<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27 0027
 * Time: 13:04
 */

namespace app\admin\model;

use think\Model;
use think\Db;
class BnbSpecialprice extends Model
{
    // 表名
    protected $name = 'bnb_specialprice';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];


    protected static function init()
    {

    }

    public function getBnbSpePriceBid($bid){
        $today = date('Y-m-d');
        $where = [
            'bnb_id' =>$bid,
            'status'=>config('state.state_ok'),
            'end_date' => ['egt',$today]
        ];
        $data =  $this->where($where)->select();
        return $data?$data:'';
    }

}