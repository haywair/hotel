<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6 0006
 * Time: 12:00
 */

namespace app\common\base;

use app\common\model\Refund;
use think\Exception;
use app\common\model\Withdraw;
use app\common\model\Landlordinfo;
use app\common\model\Cleaninfo;
use EasyWeChat\Foundation\Application;
use app\wechat\library\Config as WxConfigService;
class UserWithdraw
{
    /**
     *  自动为已审核提现转账
     *
     */
    public function WaitingWithdrawOrder()
    {
        $now = time();

        // 根据当前时间，获取不同的订单列表
        $withdrawTime = config('setting.withdraw_auto_day');
        $whereTime = $now - $withdrawTime*3600*24;

        $withdrawlist = (new Withdraw())->getWaitingWithdrawList($whereTime);
        $wxapp = new Application(WxConfigService::load());
        if ($withdrawlist) {
            foreach ($withdrawlist as $ol) {
                $this->doWithdraw($wxapp,$ol['id'],$admin_id = 0);
            }

        }
    }
    /**
     * 提现操作
     * @param $wxapp
     * @param $withdrawID
     * @param int $admin_id
     * @return Error
     */
    public function doWithdraw($wxapp,$withdrawID,$admin_id = 0)
    {
        $error = new Error();
        $withdraw_model = new Withdraw();
        try {
            $withdraw_model->startTrans();
            $desc = '';
            $withdrawData = $withdraw_model->getWithdrawById($withdrawID);
            switch($withdrawData['type']){
                case config('setting.withdraw_cleaner_type'):
                    $model = new Cleaninfo();
                    $desc  = '保洁提现';
                    break;
                case config('setting.withdraw_landlord_type'):
                    $desc  = '房东提现';
                    $model = new Landlordinfo();
                    break;
            }
            $billData = $model->where('users_id',$withdrawData['user_id'])->find();
            if (!$withdrawData) {
                throw new Exception("获取提现信息失败", 601);
            }
            $old_status = $withdrawData['status'];

            if ($old_status != config('state.state_ok')) {
                throw new Exception("状态不正确", 602);
            }
            if ($withdrawData['withdraw_status'] == config('state.withdraw_state_fail')) {
                throw new Exception("该提现申请未通过审核", 604);
            }
            if ($withdrawData['withdraw_status'] == config('state.withdraw_state_finish')) {
                throw new Exception("该提现已完成,请勿重复提现", 605);
            }
            if($withdrawData['money'] > $billData['money_total']){
                throw new Exception("提现金额超过总结算金额", 603);
            }

            // 开始提现
            $merchant_pay = $wxapp->merchant_pay;
            $partner_trade_no = $this->genTradeNo($withdrawData['user_id']);
            if ((new WechatEmu())->isWechatEmu()) {
                $withdraw_result['result_code'] = "SUCCESS";
                $withdraw_result['refund_id'] = "Emu_Refund_id";
                $withdraw_result['Wechat_Emu'] = true;
            } else {
                $withdraw_params = [
                    'partner_trade_no'  =>  $partner_trade_no,
                    'openid'            =>  $withdrawData['wx_openid'],
                    'check_name'        =>  'FORCE_CHECK',
                    're_user_name'      =>  $withdrawData['user_nickname'],
                    'amount'            =>  $withdrawData['money'],
                    'desc'              =>  $desc
                ];
                $withdraw_result = $merchant_pay->send($withdraw_params);
            }

            if ($withdraw_result['return_code'] == "SUCCESS" && $withdraw_result['result_code'] == "SUCCESS") {
                $new_status = config('state.withdraw_state_finish');
            } else {
                $new_status = config('state.withdraw_state_failed');
                throw new Exception("提现支付失败", 606);
            }

            $now = time();
            $withdraw_order['finish_time'] = $now;
            $withdraw_order['verify_time'] = $now;
            $withdraw_order['admin_id'] = $admin_id;
            $withdraw_order['withdraw_status'] = $new_status;
            $withdraw_order['trade_no'] = $partner_trade_no;
            $r = $withdraw_model->updateWithdraw($withdrawID,$withdraw_order);
            //更新可结算金额
            $dataBill['money_total'] = $billData['money_total'] - $withdrawData['money'];
            $dataBill['money_out'] = $withdrawData['money'] + $billData['money_out'];
            $r_bill = $model->where('users_id',$withdrawData['user_id'])->update($dataBill);
            if (!$r) {
                throw new Exception("保存退款单失败", 607);
            }
            if (!$r_bill) {
                throw new Exception("更新结算信息失败", 608);
            }
            $withdraw_model->commit();
            $error->setOk($withdraw_order);

        } catch (Exception $e) {
            $withdraw_model->rollback();
            $error->setError($e->getCode(), $e->getMessage());
        }
        return $error;
    }

    /**
     * 生成退款单号 30位
     *
     * @param $user_id
     * @return string
     */
    public function genTradeNo($user_id)
    {

        $begin_string = "W"; // 1
        $userid_string = str_pad($user_id, 9, "0", STR_PAD_LEFT); // 9
        $time_string = date('YmdHis', time()); //14
        $random_string = rand(100000, 999999); //6

        $trade_no = $begin_string . $time_string . $userid_string . $random_string; //30
        return $trade_no;


    }
}