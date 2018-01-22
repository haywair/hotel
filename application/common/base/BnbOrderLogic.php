<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/1
 */

namespace app\common\base;

use app\common\model\BnbInfo;
use app\common\model\Evaluate;
use app\common\model\EvaluatePhoto;
use app\common\model\LogsOrderBnb;
use app\common\model\OrderAddonClean;
use app\common\model\OrderBnb;
use app\common\model\UserVoucher;
use app\common\model\Voucher;
use Carbon\Carbon;
use EasyWeChat\Payment\Order;
use think\Exception;

class BnbOrderLogic
{

    private $error;


    public static $OPERATE_CANCEL = "cancel";
    public static $OPERATE_DELETE = "delete";
    public static $OPERATE_PAY = "pay";
    public static $OPERATE_CLEAN = "clean";
    public static $OPERATE_FINISH = "finish";
    public static $OPERATE_EVALUATE = "evaluate";
    public static $OPERATE_ORDER_AGAIN = "orderagain";


    public function __construct()
    {
        $this->error = new Error(['code' => 500, 'text' => "未知错误", 'data' => []]);
    }

    /**
     *  生成订单
     *
     * @param $order_data
     * @return Error
     * @throws \think\exception\PDOException
     */
    public function onCreate($order_data)
    {
        $model_order = new OrderBnb();

        try {
            if ($order_data) {

                $model_order->startTrans();

                $order_id = $model_order->createOrder($order_data);
                if ($order_id) {
                    // 优惠券信息
                    if ($order_data['user_voucher_id']) {
                        $vrt = (new UserVoucher())->setVoucherState($order_data['user_voucher_id'], UserVoucher::$VoucherStatus_Used, $order_id);
                        if (!$vrt) {
                            throw new Exception('使用优惠券失败', 300);
                        }

                        // 优惠券使用数量+1
                        (new Voucher())->useVoucher($order_data['voucher_id'], 1);

                    }
                    //订单记录+1
                    $r = (new BnbInfo())->addBnbOrderNumbers($order_data['bnb_id']);

                    // 添加日志
                    (new LogsOrderBnb())->addLogs($order_id, $order_data['order_sn'], $order_data['bnb_id'], $order_data['status'], 0, $order_data['user_id'], 0, LogsOrderBnb::$Operate_Create);

                } else {
                    throw new Exception('生成订单错误', 300);
                }

                $model_order->commit();

                $this->error->setOk($order_id);

            } else {
                throw new Exception("获取订单数据错误", 501);
            }
        } catch (Exception $e) {

            $model_order->rollback();

            $code = $e->getCode();
            if (!$code) {
                $code = 500;
            }

            $this->error->setError($code, $e->getMessage());
        }
        return $this->error;
    }

    /**
     * 取消订单， 如果付款后，进行判断是否自动退款，如果不自动退款，则生成人工退款单
     *
     * @param $bnb_order_sn
     * @param int $user_id
     * @param int $admin_id
     * @return Error
     * @throws \think\exception\PDOException
     */
    public function onCancel($bnb_order_sn, $user_id = 0, $admin_id = 0)
    {

        $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_CANCEL, $user_id, $admin_id);
        if ($auth->checkResult()) {
            $order = $auth->getData();

            $canceltime = $this->calcCancelTime($order['status'] , $order['in_date'] , $order['out_date'] , $order['in_hour'] , $order['out_hour']);

            // 执行取消流程
            $model_order = new OrderBnb();
            try {

                // 已经到了入住的最后一天，订单不可以取消
                if ($canceltime['state']['is_lastday']) {
                    throw new Exception('已经过了最后一天的入住时间，订单不可以取消', 510);
                }
                if($order['replaced_admin_id'] > 0){
                    throw new Exception('非本平台订单暂不支持取消订单功能', 515);
                }

                $model_order->startTrans();

                $now = time();


                if ($canceltime['state']['is_checkin']) {
                    $c = $model_order->changeOrderStatus($order['id'], BnbOrder::$OrderStatus_PartFinish, $now , $canceltime['time']['act_out'] , $canceltime['datelist']);
                }
                else {
                    $c = $model_order->changeOrderStatus($order['id'], BnbOrder::$OrderStatus_Cancel, $now);
                }

                if ($c) {

                    // 恢复优惠券
                    if ($order['user_voucher_id']) {

                        // 未到入住时间，优惠券退回，
                        if (!$canceltime['state']['is_checkin']) {
                            $v = (new UserVoucher())->setVoucherState($order['user_voucher_id'], UserVoucher::$VoucherStatus_OK, 0);
                            if (!$v) {
                                throw new Exception('恢复优惠券失败', 510);
                            }

                            // 优惠券使用数量-1
                            (new Voucher())->useVoucher($order['voucher_id'], -1);
                        } else {
                            // 超过入住时间，优惠券不退
                        }
                    }


                    // 添加日志
                    if (!$canceltime['state']['is_checkin']) {
                        (new LogsOrderBnb())->addLogs($order['id'], $order['order_sn'], $order['bnb_id'], BnbOrder::$OrderStatus_Cancel, $order['status'], $user_id, 0, LogsOrderBnb::$Operate_Cancel);
                    } else {
                        (new LogsOrderBnb())->addLogs($order['id'], $order['order_sn'], $order['bnb_id'], BnbOrder::$OrderStatus_PartFinish, $order['status'], $user_id, 0, LogsOrderBnb::$Operate_PartFinish);
                    }
                    // 取消订单，不取消订单次数


                    if ($canceltime['state']['is_paid']) {
                        // 处理退款
                        if($order['replaced_admin_id'] == 0) {
                            $refund = (new BnbRefund())->getOrderRefund($order, $canceltime);
                            if (!$refund) {
                                throw new Exception('取消订单失败，处理退款时发生错误', 510);
                            }
                        }

                        // 处理保洁订单
                        $clean = (new BnbCleanLogic())->onBnbOrderCancel($order, $canceltime);
                        if (!$clean->checkResult()) {
                            throw new Exception('取消保洁订单失败', 510);
                        }
                    }

                } else {
                    throw new Exception('取消订单失败', 510);
                }

                $model_order->commit();

                $this->error->setOk();
            } catch (Exception $e) {
                $model_order->rollback();
                $this->error->setError($e->getCode(), $e->getMessage());
            }

        } else {
            $this->error = $auth;
        }

        return $this->error;
    }


    public function onDelete($bnb_order_sn, $user_id = 0, $admin_id = 0)
    {
        $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_DELETE, $user_id, $admin_id);
        if ($auth->checkResult()) {
            $order = $auth->getData();

            // 执行删除流程
            $model_order = new OrderBnb();
            try {
                if($order['replaced_admin_id'] > 0){
                    throw new Exception('非本平台订单暂不支持删除订单功能', 515);
                }
                $model_order->startTrans();

                $now = time();

                $c = $model_order->changeOrderStatus($order['id'], BnbOrder::$OrderStatus_Delete, $now);

                if ($c) {

                    // 添加日志
                    (new LogsOrderBnb())->addLogs($order['id'], $order['order_sn'], $order['bnb_id'], BnbOrder::$OrderStatus_Delete, $order['status'], $order['user_id'], 0, LogsOrderBnb::$Operate_Delete);

                } else {
                    throw new Exception('删除订单失败', 510);
                }

                $model_order->commit();

                $this->error->setOk();
            } catch (Exception $e) {
                $model_order->rollback();
                $this->error->setError($e->getCode(), $e->getMessage());
            }


        } else {
            $this->error = $auth;
        }

        return $this->error;
    }


    public function onPay($wxapp, $bnb_order_sn, $user_wx_openid = "", $user_id = 0, $admin_id = 0)
    {
        if ($user_wx_openid != "") {
            $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_PAY, $user_id, $admin_id);
            if ($auth->checkResult()) {
                $order = $auth->getData();


                // 生成支付单
                $payorder = new BnbPay();
                $pay = $payorder->createPayOrder($user_id, $bnb_order_sn);
                if ($pay->checkResult()) {
                    $payorder = $pay->getData();

                    $wx_order = [];
                    $wx_order['trade_type'] = "JSAPI";
                    $wx_order['body'] = "民宿订单" . $order['order_sn'];
                    $wx_order['attach'] = $order['order_sn'];
                    $wx_order['out_trade_no'] = $payorder['pay_sn'];
                    $wx_order['total_fee'] = $order['pay_total'] * 100;
                    $wx_order['product_id'] = $order['bnb_id'];
                    $wx_order['openid'] = $user_wx_openid;

                    // 微信支付 统一下单

                    $payment = $wxapp->payment;
                    $wxorder = new Order($wx_order);
                    $result = $payment->prepare($wxorder);

                    if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                        $prepayId = $result->prepay_id;

                        $jssdk_config = $payment->configForPayment($prepayId); // 返回 json 字符串，如果想返回数组，传第二个参数 false

                        $this->error->setOk($jssdk_config);

                    } else {
                        $this->error->setError('534', "微信支付返回信息不正确");
                    }

                } else {
                    $this->error = $pay;
                }
            } else {
                $this->error = $auth;
            }
        } else {
            $this->error->setError('533', "无法获取用户微信id");
        }
        return $this->error;
    }


    public function onFinish($bnb_order_sn, $user_id = 0, $admin_id = 0)
    {
        $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_FINISH, $user_id, $admin_id);
        if ($auth->checkResult()) {
            $order = $auth->getData();


            // 执行删除流程
            $model_order = new OrderBnb();
            try {

                $model_order->startTrans();

                $now = time();

                $c = $model_order->changeOrderStatus($order['id'], BnbOrder::$OrderStatus_Finish, $now);

                if ($c) {

                    // 添加日志
                    (new LogsOrderBnb())->addLogs($order['id'], $order['order_sn'], $order['bnb_id'], BnbOrder::$OrderStatus_Finish, $order['status'], $order['user_id'], 0, LogsOrderBnb::$Operate_Finish);

                } else {
                    throw new Exception('完成订单失败', 510);
                }

                $model_order->commit();

                $this->error->setOk();
            } catch (Exception $e) {
                $model_order->rollback();
                $this->error->setError($e->getCode(), $e->getMessage());
            }


        } else {
            $this->error = $auth;
        }

        return $this->error;
    }


    public function onEvaluate($bnb_order_sn, $user_id = 0, $admin_id = 0)
    {
        $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_EVALUATE, $user_id, $admin_id);
        if ($auth->checkResult()) {
            $order = $auth->getData();

            if ($order['is_evaluate']) {
                $this->error->setError('502', '订单已经评价过了');
            } else {
                $this->error->setOk(['order' => $order]);
            }

        } else {
            $this->error = $auth;
        }

        return $this->error;
    }


    public function onDoEvaluate($bnb_order_sn, $user_id, $admin_id, $score_array, $photo_array, $evalute_text)
    {
        $evaluate = $this->onEvaluate($bnb_order_sn, $user_id, $admin_id);
        if ($evaluate->checkResult()) {

            $order = $evaluate->getData()['order'];

            $eval_photos = "";
            $eval_photoNum = 0;

            if ($photo_array) {
                $eval_photos = $photo_array;
                $eval_photoNum = count($eval_photos);
            }

            $max_evalute_photo_nums = config('setting.upload_evaluate_photo_num');

            if ($eval_photoNum > $max_evalute_photo_nums) {
                $this->error->setError("522", "评论图片最多上传" . $max_evalute_photo_nums . "张");
            } else {

                $eval_score = 5;

                $eval_score_room = 5;
                $eval_score_traffic = 5;
                $eval_score_clean = 5;

                if (count($score_array) > 0) {
                    $eval_score = number_format((array_sum($score_array)) / count($score_array), 1);

                    $eval_score_room = $score_array[0] ?? 5;
                    $eval_score_traffic = $score_array[1] ?? 5;
                    $eval_score_clean = $score_array[2] ?? 5;
                }

                $eval_text = config('setting.upload_evaluate_text');
                $text = trim(strip_tags($evalute_text));
                if ($text != "") {
                    $eval_text = $text;
                }

                // 获取bnb原来评分信息
                $bnbinfo = (new BnbInfo())->getBnbInfo($order['bnb_id']);


                $model_order = new OrderBnb();
                try {
                    $model_order->startTrans();
                    // 修改订单
                    $ed = $model_order->updateOrderBySn($order['order_sn'], ['is_evaluate' => 1]);
                    if (!$ed) {
                        throw new Exception("修改订单评价出现错误", 532);
                    }

                    // 保存评价数据

                    $evaluateData = [
                        'bnb_id' => $order['bnb_id'],
                        'evaluate' => $eval_text,
                        'user_id' => $order['user_id'],
                        'order_sn' => $order['order_sn'],
                        'score' => $eval_score,
                        'photos' => $eval_photoNum,
                        'score_room' => $eval_score_room,
                        'score_traffic' => $eval_score_traffic,
                        'score_clean' => $eval_score_clean,
                    ];


                    $eval_id = (new Evaluate())->insertGetId($evaluateData);
                    if (!$eval_id) {
                        throw new Exception("保存订单评价数据出现错误", 532);
                    }

                    $photoData = [];
                    for ($i = 0; $i < $eval_photoNum; $i++) {
                        $photoData[$i] = [
                            'evaluate_id' => $eval_id,
                            'bnb_id' => $order['bnb_id'],
                            'order_sn' => $order['order_sn'],
                            'user_id' => $order['user_id'],
                            'photo' => $eval_photos[$i]
                        ];
                    }

                    $e_photo = (new EvaluatePhoto())->saveAll($photoData);

                    // 计算总分

                    $ae_total_score = $bnbinfo['score_total'] + $eval_score;
                    $ae_numbers_score = $bnbinfo['numbers_score'] + 1;
                    $ae_score_point = $ae_total_score / $ae_numbers_score;

                    (new BnbInfo())->where('id', $bnbinfo['id'])->update(['score_total' => $ae_total_score, 'numbers_score' => $ae_numbers_score, 'score_point' => $ae_score_point]);

                    // 记录日志
                    (new LogsOrderBnb())->addLogs($order['id'], $order['order_sn'], $order['bnb_id'], BnbOrder::$OrderStatus_Finish, $order['status'], $order['user_id'], 0, LogsOrderBnb::$Operate_Evaluate);

                    $model_order->commit();

                    $this->error->setOk();

                } catch (Exception $e) {
                    $model_order->rollback();
                    $this->error->setError($e->getCode(), $e->getMessage());
                }
            }
        } else {
            $this->error = $evaluate;
        }
        return $this->error;
    }


    public function onClean($bnb_order_sn, $user_id = 0, $admin_id = 0)
    {
        $auth = $this->checkAuthority($bnb_order_sn, self::$OPERATE_CLEAN, $user_id, $admin_id);
        if ($auth->checkResult()) {
            $this->error = $auth;
        } else {
            $this->error = $auth;
        }

        return $this->error;
    }


    private function checkAuthority($bnb_order_sn, $operate, $user_id = 0, $admin_id = 0)
    {
        $operator = "system";
        if ($user_id > 0) {
            $operator = "user";
        }
        if ($admin_id > 0) {
            $operator = "admin";
        }


        $error = new Error();

        $orderinfo = (new OrderBnb())->getOrderBySn($bnb_order_sn);
        if ($orderinfo) {
            // 判断订单状态
            if (in_array($operate, array_keys(($this->getOperateList())[$orderinfo['status']][$operator]))) {

                $userverify = false;

                if ($user_id != 0) {
                    if ($orderinfo['user_id'] == $user_id) {
                        $userverify = true;
                    }
                } else {
                    $userverify = true;
                }

                if ($userverify) {

                    $error->setOk($orderinfo);

                } else {
                    $error->setError(500, '用户没有权限操作此订单');
                }

            } else {
                $error->setError(500, '订单状态不正确');
            }

        } else {
            $error->setError(500, '订单信息没有找到');
        }

        return $error;
    }

    //订单状态对应的操作列表


    public function getOperateList()
    {
        $operate = [];
        $operate[BnbOrder::$OrderStatus_Cancel] = [
            'user' => [                                 //用户可进行的操作
                self::$OPERATE_DELETE => '删除订单',
            ],

            'system' => [],

            'order' => '订单取消',                         // 订单状态显示
        ];
        $operate[BnbOrder::$OrderStatus_unVerify] = [
            'user' => [
                self::$OPERATE_CANCEL => '取消订单',
            ],
            'system' => [],
            'order' => '等待审核',
        ];
        $operate[BnbOrder::$OrderStatus_unPay] = [
            'user' => [
                self::$OPERATE_CANCEL => '取消订单',
                self::$OPERATE_PAY => '去支付',
            ],
            'system' => [self::$OPERATE_CANCEL],
            'order' => '等待付款',
        ];
        $operate[BnbOrder::$OrderStatus_Paid] = [
            'user' => [
                self::$OPERATE_CANCEL => '取消订单',
                self::$OPERATE_CLEAN => '保洁',
            ],
            'admin' => [
                self::$OPERATE_CANCEL => '取消订单',
                self::$OPERATE_CLEAN => '保洁',
            ],
            'system' => [
                self::$OPERATE_FINISH => '完成订单',
            ],
            'order' => '付款完成',
        ];
        $operate[BnbOrder::$OrderStatus_PasswordSent] = [
            'user' => [
                self::$OPERATE_CANCEL => '取消订单',
                self::$OPERATE_CLEAN => '保洁',
            ],
            'system' => [
                self::$OPERATE_FINISH => '完成订单',
            ],
            'order' => '准备入住',
        ];

        $operate[BnbOrder::$OrderStatus_PartFinish] = [
            'user' => [
                self::$OPERATE_EVALUATE => '评价订单',
                self::$OPERATE_ORDER_AGAIN => '再次预订',
            ],
            'system' => [
                self::$OPERATE_EVALUATE => '评价订单',
            ],
            'order' => '部分完成',
        ];

        $operate[BnbOrder::$OrderStatus_Finish] = [
            'user' => [
                self::$OPERATE_EVALUATE => '评价订单',
                self::$OPERATE_ORDER_AGAIN => '再次预订',
            ],
            'system' => [
                self::$OPERATE_EVALUATE => '评价订单',
            ],
            'order' => '订单完成',
        ];

        return $operate;
    }

    private function calcCancelTime($order_status , $order_in_date, $order_out_date, $order_in_hour, $order_out_hour )
    {
        $in = Carbon::createFromFormat("Y-m-d H:i:s", $order_in_date . " " . $order_in_hour);
        $thistime = Carbon::now();
        $out = Carbon::createFromFormat("Y-m-d H:i:s", $order_out_date . " " . $order_out_hour);
        $out_before = Carbon::createFromFormat("Y-m-d H:i:s", $order_out_date . " " . $order_out_hour)->addDay(-1);
        $begin_point = Carbon::now()->addHours(config('setting.autorefund_max_hour'));


        $is_paid = false;
        if (($order_status == BnbOrder::$OrderStatus_Paid)  || ($order_status == BnbOrder::$OrderStatus_PasswordSent))
        {
            $is_paid = true;
        }


        $act_out_time = null;

        $state = [];

        $state['is_paid'] = $is_paid;      // 是否已经支付
        $state['is_checkin'] = false;   // 是否入住
        $state['is_lastday'] = false;   // 是否最后一天
        $state['is_autorefund_time'] = false; // 是否不到自动退款时间


        $in_datelist= [];


        if ($state['is_paid']) {
            if ($thistime->lt($in)) { // 未到入住时间

                if ($begin_point->lt($in)) {
                    $state['is_autorefund_time'] = true;
                }

            } else {

                $state['is_checkin'] = true;

                if ($thistime->gt($out_before)) {
                    $state['is_lastday'] = true;
                }

                // 计算实际离店时间
                $act_out_time = Carbon::createFromFormat("Y-m-d H:i:s", date("Y-m-d ", $thistime->timestamp) . $order_out_hour);

                if ($act_out_time->lte($thistime)) {
                    $act_out_time = $act_out_time->addDay();
                }

                // 计算实际入住日期
                $incal = $in->copy();
                while (($incal->lte($act_out_time)) && ($out->gte($act_out_time))) {
                    $in_datelist[] = $in->toDateString();
                    $incal = $incal->addDay();
                }

            }
        }

        $ct = [];
        $ct['time']['in'] = $in;
        $ct['time']['now'] = $thistime;
        $ct['time']['out'] = $out;
        $ct['time']['act_out'] = $act_out_time;
        $ct['state'] = $state;
        $ct['datelist'] = $in_datelist;
        return $ct;
    }
}