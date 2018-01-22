<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/3
 */

namespace app\common\model;

use app\common\base\BnbPrice;
use Carbon\Carbon;
use think\Db;
use think\Exception;
use think\Model;

class Bnb extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public static $BnbStatus_Deleted = -1;
    public static $BnbStatus_Disabled = 0;
    public static $BnbStatus_OK = 1;
    public static $BnbStatus_Recommond = 2;


    public function getBnb($bnb_id)
    {
        if ($bnb_id) {
            return $this->where('id', "=", $bnb_id)->find();
        }
        return null;
    }

    /**
     * 查询房东所拥有的民宿
     */
    public function getLandlordBnb($landlord, $state = [])
    {
        if ($landlord) {
            $condition['landlord_user'] = $landlord;
            if ($state) {
                $condition['status'] = array('IN', $state);
            } else {
                $condition['status'] = array('gt', config('state.state_disable'));
            }
            $data = $this->where($condition)->column('id');
            return $data ? $data : null;
        }
        return null;
    }

    /**
     * 民宿列表
     */
    public function getLandlordBnbList($condition = [], $order = 'weigh desc,id desc')
    {
        $data = $this
            ->alias('a')
            ->field('a.*,d.user_nickname as landlord_name,e.nickname as manager_name')
            ->join('__USERS__ d', 'a.landlord_user = d.id', 'left')
            ->join('__ADMIN__ e', 'a.manager_user = e.id', 'left')->where($condition)->order($order)->select();
        return $data ? $data : null;
    }

    /**
     * 民宿列表分页
     */
    public function getBnbPageList($condition = [], $pagesize = 1, $order = '', $limit = 0)
    {
        if (!$order) {
            $order = 'a.weigh desc,a.id desc';
        }
        $data = $this
            ->alias('a')
            ->field('a.*,b.province_name,c.city_name,d.user_nickname as landlord_name,d.user_avatar,d.user_mobile,e.nickname as manager_name')
            ->join('__AREA__ b', 'b.id = a.area_province_code', 'left')
            ->join('__AREA__ c', 'c.id = a.area_city_code', 'left')
            ->join('__USERS__ d', 'a.landlord_user = d.id', 'left')
            ->join('__ADMIN__ e', 'a.manager_user = e.id', 'left')
            ->where($condition)
            ->order($order)
            ->paginate($pagesize, false, ['query' => request()->param()]);
        return $data ? $data : '';
    }

    /**
     * 修改民宿信息
     */
    public function updateBnbByBID($bid, $data)
    {
        return $this->where(['id' => $bid])->update($data);
    }

    /**
     *
     *  搜索民宿列表 默认结束日期大于开始日期 ， 不统计结束日期价格
     *
     * @param string $citycode
     * @param string $keywords
     * @param string $begin_date
     * @param string $end_date
     * @param int $price_min
     * @param int $price_max
     * @param int $page
     * @param int $prepagenums
     * @return array
     */
    public function getBnbList($citycode = "370100", $keywords = "", $begin_date = "", $end_date = "", $price_min = 0, $price_max = 0, $page = 1, $prepagenums = 8)
    {

        $bnbdata = [];

        $param = [];

        $price_table_sql = "";

        $price_condition_sql = "";
        $keywords_condition_sql = "";

        $city_status_condition_sql = " WHERE bnb.`status` IN ('1','2') AND bnb.`area_city_code`= :citycode ";

        $score_field = " (bnb.`status` * 10000 + bnb.`weigh` * 1000 + numbers_order * 100 + numbers_favourite *5 + score_point *100)  AS score ";

        $order_sql = " ORDER BY score DESC";

        $limit_sql = " limit :page , :prepagenums ";

        $param['citycode'] = $citycode;


        if ($keywords != "") {
            $keywords_condition_sql = "AND ( bnb.name LIKE :keywords1 OR bnb.`bnb_adwords` LIKE :keywords2 OR bnb.`demo_content` LIKE :keywords3)";
            $param['keywords1'] = "%" . $keywords . "%";
            $param['keywords2'] = "%" . $keywords . "%";
            $param['keywords3'] = "%" . $keywords . "%";
        }

        $now = time();
        $today = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $now));
        $begin = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $now));
        $end = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $now))->addDay();

        if ($begin_date) {
            try {
                $begin = Carbon::createFromFormat('Y-m-d', $begin_date);
                if ($begin->lt($today)) {
                    $begin = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $now));
                }
            } catch (\Exception $e) {

            }
        }


        if ($end_date) {
            try {
                $end = Carbon::createFromFormat('Y-m-d', $end_date);
                if ($end->lt($today)) {
                    $end = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $now))->addDay();
                }
            } catch (\Exception $e) {
            }
        }

        $begin_string = $begin->format("Y-m-d");
        $end_string = $end->format("Y-m-d");


        if (($price_min != 0) || ($price_max != 0)) {

            $price_condition_sql .= " AND price.mi>= :price_min ";
            $param['price_min'] = $price_min;
            if ($price_max != 0) {
                $price_condition_sql .= " AND price.mi <= :price_max "; //始终用最小值做比较
                $param['price_max'] = $price_max;
            }


            $week = [];

            while ($begin->lt($end)) {
                $w = $begin->dayOfWeek;
                if (!isset($week[$w])) {
                    $week[$w] = 1;
                }
                $begin = $begin->addDay();
            }

            $week = array_keys($week);


            $ps = "";
            if (($week) && (is_array($week))) {
                foreach ($week as $w) {

                    if ($w == 0) {
                        $w = 7;
                    }
                    if ($ps == "") {
                        $ps .= "SELECT p.bnb_id , p.price_w" . $w . " AS t FROM `tybnb_bnb_weekprice` AS p ";
                    } else {
                        $ps .= "UNION ALL SELECT p.bnb_id , p.price_w" . $w . " AS t FROM `tybnb_bnb_weekprice` AS p ";
                    }
                }
            }
            if ($ps) {
                $price_table_sql = "(SELECT c.bnb_id ,MAX(c.t) AS mx ,MIN(c.t) AS mi FROM (";
                $price_table_sql .= $ps;
                $price_table_sql .= ") AS c GROUP BY c.bnb_id ) AS price ";
            }
        }


        $search_sql = "";

        $count_sql = "select count(*) as counter from ";
        $select_sql = "select bnb.id ,bnb.name, bnb.bnb_adwords, bnb.room_people,bnb.room_bed, bnb.room_bedroom, bnb.bnb_image, bi.score_point , bi.numbers_score ," . $score_field . " from ";

        if ($price_condition_sql) {
            $search_sql .= $price_table_sql;
            $search_sql .= " LEFT JOIN `tybnb_bnb` AS bnb ON price.bnb_id = bnb.`id` ";
        } else {
            $search_sql .= " `tybnb_bnb` AS bnb ";
        }
        $search_sql .= " LEFT JOIN `tybnb_bnb_info` AS bi ON bi.`bnb_id` = bnb.id ";
        $search_sql .= $city_status_condition_sql;
        if ($keywords_condition_sql) {
            $search_sql .= $keywords_condition_sql;
        }

        if ($price_condition_sql) {
            $search_sql .= $price_condition_sql;
        }

        $count_sql .= $search_sql;
        $select_sql .= $search_sql;
        $select_sql .= $order_sql;
        $select_sql .= $limit_sql;


        //echo $count_sql . "<br/>";


        $bnbdata['search']['citycode'] = $citycode;
        $bnbdata['search']['keywords'] = $keywords;
        $bnbdata['search']['begin_date'] = $begin_string;
        $bnbdata['search']['end_date'] = $end_string;
        $bnbdata['search']['price_min'] = $price_min;
        $bnbdata['search']['price_max'] = $price_max;

        $bnbdata['page']['total'] = 0;
        $bnbdata['page']['page'] = $page;
        $bnbdata['page']['prepagenums'] = $prepagenums;
        $bnbdata['page']['next'] = false;
        $bnbdata['page']['nexturl'] = '';

        $bnbdata['data'] = [];


        $r = $this->query($count_sql, $param);
        if ($r) {
            $counter = $r[0]['counter'];
            if ($counter > 0) {

                $bnbdata['page']['total'] = ceil($counter / $prepagenums);
                if ($bnbdata['page']['page'] < $bnbdata['page']['total']) {
                    $bnbdata['page']['next'] = true;
                    $bnbdata['page']['nexturl'] = url('@index/index/index', array_merge($bnbdata['search'] , ['page'=>$page+1 , 'prepagenums'=>$prepagenums]));
                }

                $param['page'] = ($page - 1) * $prepagenums;
                $param['prepagenums'] = $prepagenums;

                $result = $this->query($select_sql, $param);
                if ($result) {
                    // 获取房源时间段内信息

                    $bnb_price = new BnbPrice();
                    $bnb_image_path = config('upload.upload')['thumb']['thumb1']['dir'];

                    foreach ($result as $b) {

                        $bnb = [];
                        $bnb['id'] = $b['id'];
                        $bnb['name'] = $b['name'];
                        $bnb['bnb_adwords'] = $b['bnb_adwords'];
                        $bnb['room_people'] = $b['room_people'];
                        $bnb['room_bed'] = $b['room_bed'];
                        $bnb['room_bedroom'] = $b['room_bedroom'];
                        $bnb['numbers_score'] = $b['numbers_score'];
                        $bnb['score_point'] = $b['score_point'];

                        if ($b['bnb_image']) {
                            $bnb['bnb_image'] = "/" . $bnb_image_path . "/" . $b['bnb_image'];
                        } else {
                            $bnb['bnb_image'] = "";
                        }

                        $plist = $bnb_price->getBnbPriceList($b['id'], $begin_string, $end_string, "", true, false);

                        // 是否可售
                        if (($plist) && (is_array($plist))) {
                            $min_price = 99999999;
                            $sell = true;
                            foreach ($plist as $p) {
                                if ($p['price'] <= $min_price) {
                                    $min_price = $p['price'];
                                }
                                if (!$p['sell']) {
                                    $sell = false;
                                }
                            }

                            $bnb['price'] = intval($min_price);
                            $bnb['sell'] = $sell;
                        }

                        $bnbdata['data'][] = $bnb;
                    }
                }
            }
        }


        return $bnbdata;

    }
    public function getAdminBnbs($condition=[]){
        if($condition){
            $data = $this
                -> alias('a')
                -> field('a.*,b.province_name,c.city_name,d.user_nickname as landlord_name,d.user_avatar,d
                 .user_mobile,e.nickname as manager_name')
                -> join('__AREA__ b', 'b.id = a.area_province_code', 'left')
                -> join('__AREA__ c', 'c.id = a.area_city_code', 'left')
                -> join('__USERS__ d', 'a.landlord_user = d.id', 'left')
                -> join('__ADMIN__ e', 'a.manager_user = e.id', 'left')
                -> where($condition)
                -> where('a.status','IN',[config('state.state_ok'),config('state.state_disalbe'),config('state.state_mark')])
                -> order('id desc')
                -> paginate(config('page.admin_binb_bnb_page'), false, ['query' => request()->param()]);
            return $data?$data:null;
        }
        return null;
    }

    /**
     * 城市未绑定民宿
     * @param int $citycode
     * @return array|null
     */
    public function getUnbindAdminBnbs($citycode=0){
        $data = $this->where('manager_user',0)
              -> field('id,name')
              -> where('area_city_code',$citycode)
              -> where('status','IN',[config('state.state_ok'),config('state.state_disalbe'),config('state.state_mark')])
              -> select();
        return $data?$data:null;
    }
}