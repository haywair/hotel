<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/11
 */

namespace app\common\base;

use app\common\model\Cleaninfo;
use app\common\model\OrderClean;
use app\common\model\OrderCleanPhoto;
use app\common\model\Users;
use Predis\Client;

class AutoCleaner
{

    private $redis;

    private $key_map = "";


    public function __construct()
    {
        $rs = config('redis_server');

        $this->redis = new Client([
            'host' => $rs['host'],
            'port' => $rs['port'],
            'database' => $rs['database'],
        ], ['prefix' => $rs['prefix']]);

        $this->key_map = config('redis.Key_CleanerMap');
    }


    /**
     * 根据地理位置返回最近的可用保洁员
     *
     * @param $geo_lng
     * @param $geo_lat
     * @param $clean_date
     * @return int|null
     */
    public function getCleaner($geo_lng, $geo_lat, $clean_date)
    {
        $dist = (new AutoCleaner())->findAllCleanerSort($geo_lng, $geo_lat);
        if ($dist) {
            $cid = (new AutoCleaner())->getFirstCleaner($dist, $clean_date);
            return $cid;
        }
        return null;
    }


    /**
     * 数据保存在redis中,用来计算距离
     *
     * @return bool
     */
    private function setCleanerToMap()
    {
        // 判断是否redis中是否存在，存在则直接返回
        if ($this->redis->exists($this->key_map)) {
            return true;
        } else {
            // 获取所有保洁员地点信息，保存入redis中
            $cleanerlist = (new Cleaninfo())->getAllCleanerMap();
            if ($cleanerlist) {
                $pipe = $this->redis->pipeline();
                foreach ($cleanerlist as $c) {
                    $pipe->geoadd($this->key_map, $c['geo_lng'], $c['geo_lat'], $c['user_id']);
                }
                $pipe->expire($this->key_map, config('redis.Time_CleanerMap'));
                $replies = $pipe->execute();
            }
        }
    }

    /**
     * 获取所有保洁员的相对距离
     *
     * @param $geo_lng
     * @param $geo_lat
     * @return array
     */
    private function findAllCleanerSort($geo_lng, $geo_lat)
    {
        $this->setCleanerToMap();
        $dist = $this->redis->georadius($this->key_map, $geo_lng, $geo_lat, config('redis.max_distince'), "km", array("WITHDIST" => true, "SORT" => "ASC"));
        return $dist;
    }

    /**
     * 根据保洁日期，确定最近的可用保洁员
     *
     * @param $cleaner_dist
     * @param $clean_date
     * @return int|null
     */
    private function getFirstCleaner($cleaner_dist, $clean_date)
    {
        $prepage = 2; // 每次获取多少保洁员数据

        $total = count($cleaner_dist);
        $nowpage = 0;
        if ($total > 0) {

            $model_user = new Users();

            $clean_user_id = 0;

            $roll = true;
            while ($roll) {
                $start_item = $nowpage * $prepage;

                if ($start_item >= $total) {
                    $roll = false;
                } else {
                    $end_item = ($nowpage + 1) * $prepage;
                    if ($end_item > $total) {
                        $end_item = $total;
                    }

                    $nowdist = array_slice($cleaner_dist, $start_item, ($end_item - $start_item));
                    $user_id_list = array_values(array_column($nowdist, "0"));

                    // 获取是保洁员，并且是接单状态的人员id，按照距离排序
                    $clean_user_list = $model_user->checkCleanerStatus($user_id_list);
                    if ($clean_user_list) {
                        foreach ($clean_user_list as $cu) {
                            // 检查保洁员当天是否有预订
                            $r = (new OrderClean())->checkCleanerStatus($cu, $clean_date);
                            if (!($r)) {
                                $clean_user_id = $cu;
                                $roll = false;
                                break;
                            }
                        }
                    }
                }

                $nowpage++;
            }
            // 返回可用的保洁员id
            return $clean_user_id;
        }
        return null;
    }

    /**
     * 获取订单结算费用信息
     * @param $orderData
     * @return array|null
     */
    public function reckonCleanOrderBill($orderData){
        $unverifyCleanPhoto = (new OrderCleanPhoto())->getOrderNoVefiryNum($orderData['id']);
        if($unverifyCleanPhoto > 0){
            return null;
        }
        $userScoreNum = model('OrderCleanScore')->getUserScoreNumOrderSn($orderData['order_sn']);
        $score = 0;
        $fee_cleaner = $orderData['fee_clean'];
        if( $userScoreNum >0 ){
            $score = model('OrderCleanScore')->getUserScoreTotalOrderSn($orderData['order_sn']);
            if($score){
                $fee_cleaner = $orderData['fee_clean'] * $score/100;
            }
        }
        $data = [
            'order_clean_id'    =>  $orderData['id'],
            'verify_score'      =>  $score,
            'fee_cleaner'       =>  $fee_cleaner,
            'order_sn'          =>  $orderData['order_sn'],
            'cleaner_id'        =>  $orderData['cleaner_id'],
            'bnb_id'            =>  $orderData['bnb_id']
        ];
        return $data;
    }
}