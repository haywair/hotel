<?php

namespace app\index\controller;

use app\common\base\Area;
use app\common\base\Image;
use app\common\model\Banner;
use app\common\model\Bnb;
use app\common\controller\BnbBase;
use Carbon\Carbon;
use think\Request;
class Index extends BnbBase
{

    public function index()
    {
        $search = $this->getSearchParam();

        $bnblist = (new Bnb())->getBnbList($search['citycode'], $search['keywords'], $search['begin_date'], $search['end_date'], $search['price_min'], $search['price_max'], $search['page'], $search['prepagenums']);

        $bnbdata = [];
            $bnbdata['search'] = $bnblist['search'];

        if ($bnblist) {
            $bnbpage = $this->fetch('index/sub/bnbitem', ['bnb' => $bnblist]);
            $bnbdata['page'] = $bnblist['page'];
            $bnbdata['data'] = $bnbpage;
        } else {
            $bnbdata['page'] = [];
            $bnbdata['data'] = [];
            $bnbdata['search'] = [];
        }

        if ($this->request->isAjax()) {
            echo json_encode($bnbdata);
        } else {
            $bannerData = (new Banner())->getBannerList();

            $this->assign('title', '首页');
            $this->assign('bannerData', $bannerData);
            $this->assign('citycode',$search['citycode']);
            $this->assign('bnbdata', $bnbdata);
            return $this->fetch();
        }
    }

    public function city()
    {
        $jump = Request::instance()->param("jump") ?? "0";

        $c = (new Area())->getCityList();
        $this->assign('title', '城市列表');
        $this->assign('citys', $c);
        $this->assign('jump', $jump);

        return $this->fetch();
    }

    public function search()
    {
        $search = $this->getSearchParam();
        $city = (new \app\common\model\Area())->getAreaByCityCode($search['citycode']);

        $pricelist = config('price.search_price_list');
        $default_price_id = -1;
        $price_id = -1;

        $p_min = $search['price_min'];
        $p_max = $search['price_max'];

        if (($pricelist) && (is_array($pricelist))) {
            foreach ($pricelist as $key => $item) {

                if ($item['default'] == 1) {
                    $default_price_id = $key;
                }
                if (($item['price'][0] == $p_min) && ($item['price'][1] == $p_max)) {
                    $price_id = $key;
                    break;
                }
            }

            if ($default_price_id != $price_id) {
                $pricelist[$default_price_id]['default'] = 0;
                $pricelist[$price_id]['default'] = 1;
            }
        }

        $this->assign('title', '搜索');
        $this->assign('city', $city);
        $this->assign('search', $search);
        $this->assign('pricelist', $pricelist);

        $dt = new Carbon();
        $this->assign('today', $dt->toDateString());
        $dt->addDays(config('setting.price_max_day'));
        $this->assign('max_date', $dt->toDateString());

        return $this->fetch();
    }

    private function getSearchParam()
    {
        $dt = new Carbon();

        $params = Request::instance()->param();

        $search['citycode'] = $params['citycode'] ?? session(config('session.CityCode'));
        $search['keywords'] = $params['keywords'] ?? "";
        $search['begin_date'] = $params['begin_date'] ?? $dt->toDateString();
        $dt->addDay();
        $search['end_date'] = $params['end_date'] ?? $dt->toDateString();
        $search['price_min'] = $params['price_min'] ?? 0;
        $search['price_max'] = $params['price_max'] ?? 0;
        $search['page'] = $params['page'] ?? 1;
        $search['prepagenums'] = $params[''] ?? config('page.index_bnb_page_size');

        return $search;
    }
}
