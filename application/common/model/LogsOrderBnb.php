<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/1
 */

namespace app\common\model;

use app\admin\model\Admin;
use Carbon\Carbon;
use think\Model;

class LogsOrderBnb extends Model
{

    public static $Operate_Create = "create";
    public static $Operate_Cancel = "cancel";
    public static $Operate_Delete = "delete";
    public static $Operate_Pay = "pay";
    public static $Operate_Finish = "finish";
    public static $Operate_PartFinish = "partfinish";
    public static $Operate_Evaluate = "evaluate";


    public function addLogs($bnb_order_id, $bnb_order_sn, $bnb_id, $new_status, $old_status, $user_id, $admin_id, $operate)
    {
        $data = [];
        $data['bnb_order_id'] = $bnb_order_id;
        $data['bnb_order_sn'] = $bnb_order_sn;
        $data['bnb_id'] = $bnb_id;
        $data['user_id'] = $user_id;
        $data['admin_id'] = $admin_id;
        $data['operate'] = $operate;
        $data['createtime'] = time();
        $data['oldstatus'] = $old_status;
        $data['newstatus'] = $new_status;
        $data['demo'] = $this->createText($new_status, $old_status, $user_id, $admin_id, $operate, $data['createtime']);
        $this->insert($data);

    }


    public function createText($new_status, $old_status, $user_id, $admin_id, $operate, $time)
    {
        $order_status = config('state.orderState');

        $text = "";

        $text_user = "";
        $text_time = Carbon::createFromTimestamp($time)->toDateTimeString();
        $text_operate = "";
        $text_status = "";

        if ($admin_id != 0) {
            $admin = (new Admin())->where('id', "=", $admin_id)->find();
            if ($admin) {
                $text_user = "管理员：" . $admin['truename'] . "(" . $admin_id . ")";
            } else {
                $text_user = "未知管理员(" . $admin_id . ")";
            }
        } else if ($user_id != 0) {
            $user = (new Users())->getUserById($user_id);
            if ($user) {
                $text_user = "用户：" . $user['user_nickname'] . "(" . $user['id'] . ")";
            } else {
                $text_user = "未知用户(" . $user_id . ")";
            }
        } else {
            $text_user = "系统(0)";
        }


        switch ($operate) {
            case self::$Operate_Create:
                $text_operate = "创建订单";
                break;
            case self::$Operate_Cancel:
                $text_operate = "取消订单";
                break;
            case self::$Operate_Delete:
                $text_operate = "删除订单";
                break;
            case self::$Operate_Evaluate:
                $text_operate = "评价订单";
                break;
            case self::$Operate_Pay:
                $text_operate = "支付订单";
                break;
            case self::$Operate_PartFinish:
                $text_operate = "部分完成";
                break;
            case self::$Operate_Finish:
                $text_operate = "完成订单";
                break;
            default:
                $text_operate = "未知操作";
                break;
        }

        if ($operate == self::$Operate_Create) {
            $text_status = "创建订单";
        } else {
            $old_text = $order_status[$old_status] ?? "未知状态";
            $new_text = $order_status[$new_status] ?? "未知状态";
            $text_status = "由(" . $old_text . ")变为(" . $new_text . ")";
        }

        $text = "时间：" . $text_time . " 操作人：" . $text_user . " 事件：" . $text_operate . " 状态：" . $text_status;
        return $text;
    }
}