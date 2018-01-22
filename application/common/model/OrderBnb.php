<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/3
 */

namespace app\common\model;

use app\common\base\BnbOrder;
use think\Model;

class OrderBnb extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public static $DepositStatus_Nocash = 0;
    public static $DepositStatus_Cashed = 1;
    public static $DepositStatus_Partrefund = 2;
    public static $DepositStatus_Refund = 3;

    public function getOrderById($id)
    {
        if ($id) {
            $list = $this
                ->alias('a')
                ->field('a.*,b.province_name,c.city_name,d.user_nickname,e.name')
                ->join('__AREA__ b', 'b.id = a.province_code', 'left')
                ->join('__AREA__ c', 'c.id = a.city_code', 'left')
                ->join('__USERS__ d', 'd.id = a.user_id', 'left')
                ->join('__BNB__ e', 'e.id = a.bnb_id', 'left')
                ->where('a.id', '=', $id)
                ->find();
            return $list ? $list : null;
        } else {
            return null;
        }
    }

    /**
     * 创建用户订单数据，返回订单id
     * @param $orderdata
     * @return int|string
     */
    public function createOrder($orderdata)
    {
        if ($orderdata) {
            $order_id = $this->insertGetId($orderdata);
            return $order_id;
        }
        return null;
    }

    /**
     * 根据民宿id，时间段 查询订单，用于计算房价是否可用
     *
     * @param $bnb_id
     * @param $start_date
     * @param $end_date
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getOrderList($bnb_id, $start_date, $end_date)
    {
        $where = [];
        $where['bnb_id'] = $bnb_id;

        $where['status'] = ['>', BnbOrder::$OrderStatus_Cancel];
        $where['bnb_id'] = $bnb_id;


        $wheredate_in = function ($query) use ($start_date, $end_date) {
            $query->where("in_date", '>=', $start_date)->where('in_date', "<", $end_date);
        };

        $wheredate_out = function ($query) use ($start_date, $end_date) {
            $query->where("live_out_date", '>', $start_date)->where('live_out_date', "<=", $end_date);
        };

        $wheredatex = function ($query) use ($start_date, $end_date) {
            $query->where('in_date', '<', $start_date)->where('live_out_date', '>=', $end_date);
        };


        $list = $this->where($where)->where(function ($query) use ($wheredatex, $wheredate_in, $wheredate_out) {
            $query->whereor($wheredate_in)->whereor($wheredate_out)->whereor($wheredatex);
        })->order('createtime', 'asc')->select();

        return $list;
    }


    public function getOrderListBySnList($order_sn_list)
    {

        if ($order_sn_list) {
            $where = [];
            $where['order_sn'] = ['in', $order_sn_list];
            $orderlist = $this->where($where)->select();

            $data = [];
            if ($orderlist) {
                foreach ($orderlist as $order) {
                    $d['id'] = $order['id'];
                    $d['status'] = $order['status'];
                    $d['user_id'] = $order['user_id'];
                    $d['pay_total'] = $order['pay_total'];
                    $data[$order['order_sn']] = $d;
                }
            }

            return $data;
        }
        return null;
    }

    public function getOrderBySn($order_sn)
    {
        if ($order_sn) {
            $where = [];
            $where['order_sn'] = $order_sn;
            return $this->where($where)->find();
        }

        return null;
    }

    /**
     * 获取民宿订单数量
     */
    public function getOrderBnbNum($condition = [])
    {
        return $this->where($condition)->count();
    }

    /**
     * 按日期统计订单数量
     * @param string $begin_time
     * @param string $end_time
     * @param array $state
     * @return array
     */
    public function getOrderNumGroupByOrdertime($begin_time = '', $end_time = '', $state = [])
    {
        $where = [];
        if ($begin_time && $end_time) {
            $where['order_time'] = array('between', $begin_time . ',' . $end_time);
        }
        if ($state) {
            $where['status'] = array('IN', $state);
        }

        $data = $this->field("FROM_UNIXTIME(order_time,'%Y-%m-%d') as day,order_total,count(*) as number,order_time")
            ->where($where)->group('day')->order('order_time asc')->select();
        $orderData = [];
        foreach ($data as $k => $v) {
            $orderData[$v['day']] = $v['number'];
        }
        return $orderData;
    }

    /**
     * 今日下单数量
     * @return int|string
     */
    public function getTodayOrderNum()
    {
        $begin_time = strtotime(date('Y-m-d'));
        $end_time = $begin_time + 24 * 3600;
        $where['order_time'] = array('between', $begin_time . ',' . $end_time);
        return $this->where($where)->count();
    }

    /**
     * 未处理订单数量
     * @return int|string
     */
    public function getUnsettledOrderNum()
    {
        $where['status'] = config('state.order_create_state');
        return $this->where($where)->count();
    }

    /**
     * 总订单金额
     * @return mixed
     */
    public function getOrderTotalMoney()
    {
        $orders = $this->field('sum(order_total) as money')->where('status', config('state.order_finish_state'))->select();
        return $orders[0]['money'];
    }

    /**
     * 根据用户id取得订单
     * @param $userId
     * @param bool $state
     * @return false|null|\PDOStatement|string|\think\Collection
     */
    public function getOrderByUID($userId, $state = true, $page = null)
    {
        $where = [];
        $where['a.user_id'] = $userId;
        if ($state) {
            $where['a.out_date'] = array('egt', date('Y-m-d'));
        } else {
            $where['a.out_date'] = array('lt', date('Y-m-d'));
        }
        $where['a.status'] = array('not in', [strval(config('state.order_del_state')), strval(config('state.order_cancel_state'))]);
        $orderData = $this->alias('a')
            ->field('a.*,b.name,b.bnb_image,b.room_people')
            ->join('__BNB__ b', 'a.bnb_id = b.id', 'left')
            ->where($where)
            ->order('id desc')
            //->select();
            ->paginate($page);
        return $orderData ? $orderData : null;
    }

    public function getOrderByBnb($bnbId, $state = null)
    {
        $where = [];
        $where['a.bnb_id'] = $bnbId;
        if ($state) {
            $where['a.status'] = array('IN', $state);
        }
        $orderData = $this->alias('a')
            ->field('a.*,b.name,b.bnb_image,b.room_people,c.user_nickname')
            ->join('__BNB__ b', 'a.bnb_id = b.id', 'left')
            ->join('__USERS__ c', 'a.user_id = c.id', 'left')
            ->where($where)
            ->order('id desc')
            ->select();
        return $orderData ? $orderData : null;
    }

    /**
     * 更新订单
     * @param $orderId
     * @return $this
     */
    public function updateOrderByID($orderId, $data)
    {
        return $this->where('id', $orderId)->update($data);
    }

    /**
     * 更新订单
     * @param $order_sn
     * @return $this
     */
    public function updateOrderBySn($order_sn, $data)
    {
        return $this->where('order_sn', $order_sn)->update($data);
    }

    /**
     * 指定日期内下单已支付的用户数量
     * @param $begin_date
     * @param $end_date
     * @param array $state
     * @return int|string
     */
    public function getOrdersUserNum($begin_date, $end_date)
    {
        $state = [config('state.order_pay_state'), config('state.order_msg_state'), config('state.order_finish_state')];
        $state = implode(',', $state);
        $count = $this->where('in_date', '>=', $begin_date)
            ->where('out_date', '<=', $end_date)
            ->where('status', 'in', $state)
            ->group('user_id')->count();
        return $count ? $count : 0;
    }

    /**
     * 指定日期内的订单总额
     * @param $begin_date
     * @param $end_date
     * @return false|int|\PDOStatement|string|\think\Collection
     */
    public function getOrderTotal($begin_date = '', $end_date = '', $state = [])
    {
        $wheredate_in = [];
        $wherestate_in = [];
        if ($begin_date && $end_date) {
            $wheredate_in = function ($query) use ($begin_date, $end_date) {
                $query->where("in_date", '>=', $begin_date)->where('out_date', "<=", $end_date);
            };
        }
        if ($state) {
            $state = implode(',', $state);
            $wherestate_in = function ($query) use ($state) {
                $query->where("status", 'in', $state);
            };
        }
        $total = $this->field('sum(order_total) as total')->where(function ($query) use ($wheredate_in) {
            $query->where($wheredate_in);
        })->where(function ($query) use ($wherestate_in) {
            $query->where($wherestate_in);
        })->select();
        return $total[0]['total'] ? $total[0]['total'] : 0;
    }

    /**
     * 优惠券订单
     * @param $voucherId
     * @param int $limit
     * @return false|null|\PDOStatement|string|\think\Collection
     */
    public function getOrderByVoucher($voucherId, $limit = 0)
    {
        if ($voucherId) {
            $list = $this
                ->alias('a')
                ->field('a.*,e.name')
                ->join('__BNB__ e', 'e.id = a.bnb_id', 'left')
                ->where('a.voucher_id', '=', $voucherId)
                ->where('a.status', '=', config('state.order_finish_state'))
                ->order('a.finish_time desc')
                ->limit($limit)
                ->select();
            return $list ? $list : null;
        } else {
            return null;
        }
    }


    public function changeOrderStatus($order_id, $newstatus, $time , $act_out = null , $act_datelist=null)
    {
        $data = [];
        $data['status'] = $newstatus;
        $data['updatetime'] = $time;

        if ($newstatus == BnbOrder::$OrderStatus_PartFinish)
        {
            if (($act_out) && ($act_datelist))
            {
                $data['live_out_date'] = $act_out->toDateString();
                $data['live_night'] = count($act_datelist);
            }
        }

        return $this->where('id', '=', $order_id)->update($data);
    }


    public function getOrderListByUserId($userid, $page = 1, $nums = 6, $now = 1)
    {
        $state = [BnbOrder::$OrderStatus_unVerify, BnbOrder::$OrderStatus_unPay, BnbOrder::$OrderStatus_Paid, BnbOrder::$OrderStatus_PasswordSent];
        if (!$now) {
            $state = [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish];
        }

        $q = $this->where('user_id', $userid);

        if ($now) {
            $q->where('status', 'in', $state)->whereor(function ($query) {
                $query->where('status', 'in', [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish])->where("is_evaluate", 0);
            });
        } else {
            $q->where(function ($query) {
                $query->where(function ($qand) {
                    $qand->where('status', 'in', [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish])->where('is_evaluate', 1);
                })->whereor(function ($qor) {
                    $qor->where('status', BnbOrder::$OrderStatus_Cancel);
                });
            });
        }

        $r = $q->order("createtime desc")->paginate($nums, false, ['query' => ['page', $page]]);
        $v = $this->getLastSql();
        return $r;
    }

    public function getOrderListByBnbId($bnbId, $page = 1, $nums = 6, $now = 1)
    {
        $state = [BnbOrder::$OrderStatus_unVerify, BnbOrder::$OrderStatus_unPay, BnbOrder::$OrderStatus_Paid, BnbOrder::$OrderStatus_PasswordSent];
        if (!$now) {
            $state = [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish];
        }

        $q = $this->where('bnb_id', $bnbId);

        if ($now) {
            $q->where('status', 'in', $state)->whereor(function ($query) {
                $query->where('status', 'in', [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish])->where("is_evaluate", 0);
            });
        } else {
            $q->where(function ($query) {
                $query->where(function ($qand) {
                    $qand->where('status', 'in', [BnbOrder::$OrderStatus_Finish, BnbOrder::$OrderStatus_PartFinish])->where('is_evaluate', 1);
                })->whereor(function ($qor) {
                    $qor->where('status', BnbOrder::$OrderStatus_Cancel);
                });
            });
        }

        $r = $q->order("createtime desc")->paginate($nums, false, ['query' => ['page', $page]]);
        //$v = $this->getLastSql();
        return $r;
    }

    /**
     * 保存收款信息
     * @param $order_sn
     * @param $pay_sn
     * @param $trade_no
     * @param $pay_time
     */
    public function saveOrderBnbPayInfo($order_sn, $pay_sn = '', $trade_no = '', $pay_time = '')
    {
        if ($order_sn) {
            $data = [
                'pay_sn' => $pay_sn,
                'trade_no' => $trade_no,
                'pay_time' => $pay_time,
                'status' => config('state.order_pay_state')
            ];
            $res = $this->where('order_sn', $order_sn)->update($data);
            return $res ? $res : null;
        }
        return null;
    }
}