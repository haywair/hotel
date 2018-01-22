<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/28 0028
 * Time: 10:56
 */

namespace app\common\model;

use think\model;

class BnbWeekprice extends Model
{
    public function getPriceByBnbId($bnb_id)
    {
        if ($bnb_id) {
            $where = [];
            $where['bnb_id'] = $bnb_id;
            $price = $this->where($where)->find();

            if ($price) {
                $bnbprice = [];
                for ($i = 1; $i <= 6; $i++) {
                    $bnbprice[$i] = $price['price_w' . $i];
                }
                $bnbprice[0] = $price['price_w7'];
                return $bnbprice;
            }
        }

        return null;
    }
}