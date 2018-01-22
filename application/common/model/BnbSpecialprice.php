<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27 0027
 * Time: 13:04
 */

namespace app\common\model;

use think\Model;

class BnbSpecialprice extends Model
{
    public function getBnbSpecailPrice($bnb_id, $start_date, $end_date)
    {
        if ($bnb_id) {
            $where = [];
            $where['status'] = 1;
            $where['bnb_id'] = $bnb_id;

            $wheredate = [];

            $wheredate['begin_date'] = ['between time', [$start_date, $end_date]];
            $wheredate['end_date'] = ['between time', [$start_date, $end_date]];

            $wheredatex = [];
            $wheredatex['begin_date'] = ['<=', $start_date];
            $wheredatex['end_date'] = ['>=', $end_date];


            $list = $this->where($where)->where(function ($query) use ($wheredate, $wheredatex) {
                $query->whereor($wheredate)->whereor(function ($q) use ($wheredatex) {
                    $q->where($wheredatex);
                });
            })->order('createtime', 'asc')->select();

            $pricelist = [];

            foreach ($list as $p) {
                $price = [];
                $price['name'] = $p['name'];
                $price['begin_date'] = $p['begin_date'];
                $price['end_date'] = $p['end_date'];
                $price['price'] = $p['price'];
                $pricelist[] = $price;
            }

            return $pricelist;
        }

        return null;
    }

}