<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/2
 */

namespace app\common\base;

use app\common\model\BnbSpecialprice;
use app\common\model\BnbWeekprice;
use app\common\model\OrderBnb;
use Carbon\Carbon;

class BnbPrice
{
    /**
     * 获取时间段列表
     *
     * @param $begin_date
     * @param $end_date
     * @param string $now_date
     * @param float $price
     * @param string $name
     * @return array
     */
    public function createDateRange($begin_date, $end_date, $now_date = "", $price = 0.00, $name = "", $include_end_date = true)
    {
        $datelist = [];

        if ($now_date == "") {
            $now_date = date('Y-m-d');
        }

        $begin = Carbon::createFromFormat('Y-m-d', $begin_date);
        $end = Carbon::createFromFormat('Y-m-d', $end_date);
        $now = Carbon::createFromFormat('Y-m-d', $now_date);

        if ($include_end_date) {
            $f = $begin->lte($end);
        } else {
            $f = $begin->lt($end);
        }
        while ($f) {
            $d = [];
            $date = $begin->toDateString();
            $d['week'] = $begin->dayOfWeek;
            $d['price'] = $price;
            $d['name'] = $name;
            if ($begin->lt($now)) {
                $d['old'] = 1;
            } else {
                $d['old'] = 0;
            }

            $d['order_id'] = 0;
            $d['sell'] = 1;

            $datelist[$date] = $d;
            $begin->addDay();

            if ($include_end_date) {
                $f = $begin->lte($end);
            } else {
                $f = $begin->lt($end);
            }
        }

        return $datelist;
    }

    /**
     * 获取bnb时间段价格列表
     * @param $bnb_id
     * @param $begin_date
     * @param $end_date
     * @param $now_date
     * @param boolean $order
     * @param $include_end_date 包含结束日期价格
     * @return mixed
     */

    public function getBnbPriceList($bnb_id, $begin_date, $end_date, $now_date = "", $order = true, $include_end_date = true)
    {

        $datelist = $this->createDateRange($begin_date, $end_date, $now_date, 0.00, '', $include_end_date);

        $weekprice = (new BnbWeekprice())->getPriceByBnbId($bnb_id);

        $specaillist = (new BnbSpecialprice())->getBnbSpecailPrice($bnb_id, $begin_date, $end_date);

        if ($order) //获取订单数据
        {
            $orderlist = (new OrderBnb())->getOrderList($bnb_id, $begin_date, $end_date);
        } else {
            $orderlist = [];
        }


        $splist = $this->genBnbPrice($datelist, $weekprice, $specaillist, $orderlist, $now_date);

        return ($splist);
    }


    public function getBnbJsDateList($bnb_id, $begin_date, $end_date, $now_date = "", $order = true)
    {
        $jl = [];
        $min_price = 0;
        $list = $this->getBnbPriceList($bnb_id, $begin_date, $end_date, $now_date, $order);
        if ($list) {
            foreach ($list as $date => $price) {
                $j = [];
                $j['date'] = $date;
                $j['price'] = intval($price['price']);
                $j['disabled'] = false;
                if (!$price['sell']) {
                    $j['disabled'] = true;
                }
                $jl[] = $j;

                if ($j['price'] != 0) {
                    if ($min_price == 0) {
                        $min_price = $j['price'];
                    } else {
                        if ($min_price > $j['price']) {
                            $min_price = $j['price'];
                        }
                    }
                }

            }
        }

        return ['start_date' => $begin_date, 'end_date' => $end_date, 'pricelist' => json_encode($jl), 'min_price' => $min_price];
    }

    /**
     * 获取时间段价格
     * @param $datelist
     * @param $weekprice
     * @param $specaillist
     * @param $now_date
     * @return mixed
     */
    private function genBnbPrice($datelist, $weekprice, $specaillist, $orderlist, $now_date)
    {

        foreach ($datelist as $date => $price) {
            $datelist[$date]['price'] = $weekprice[$price['week']];

            if ($weekprice[$price['week']] > 0) {
                $datelist[$date]['sell'] = 1;
            } else {
                $datelist[$date]['sell'] = 0;
            }

            $datelist[$date]['sell_price'] = 0.00;
            $datelist[$date]['user_id'] = 0;
        }

        foreach ($specaillist as $sp) {
            $splist = $this->createDateRange($sp['begin_date'], $sp['end_date'], $now_date, $sp['price'], $sp['name']);
            foreach ($splist as $date => $price) {

                if (isset($datelist[$date])) {
                    $datelist[$date]['price'] = $price['price'];
                    $datelist[$date]['name'] = $price['name'];
                    if (($price['price'] > 0) && ($datelist[$date]['sell'])) {
                        $datelist[$date]['sell'] = 1;
                    } else {
                        $datelist[$date]['sell'] = 0;
                    }
                }
            }
        }

        if ($orderlist) {
            foreach ($orderlist as $ol) {
                $olprice = unserialize($ol['price_list']);
                if (($olprice) && (is_array($olprice))) {
                    foreach ($olprice as $date => $value) {
                        if (isset($datelist[$date])) {
                            if ($date != $ol['live_out_date']) {
                                $datelist[$date]['sell_price'] = $value['price'];
                                $datelist[$date]['user_id'] = $ol['user_id'];
                                $datelist[$date]['sell'] = 0;
                            } else {
                                break;
                            }
                        }

                    }
                }
            }
        }

        return $datelist;
    }
}
