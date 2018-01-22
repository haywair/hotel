<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/12/4
 */

namespace app\index\controller;

use app\common\base\BnbOrder;
use app\common\base\BnbOrderLogic;
use app\common\controller\BnbBase;
use app\common\model\Landlordinfo;
use app\common\model\OrderAddonClean;
use app\common\model\Bnb;
use app\common\model\OrderCleanScore;
use think\Request;

class Order extends BnbBase
{

    public function index()
    {
        $params = Request::instance()->param();

        $page = $params['page'] ?? 1;
        $now = $params['now'] ?? 1;

        $bnborder = new BnbOrder();

        $list = [];

        if ($page == 1) {
            $list['now'] = $bnborder->getOrderListByUser($this->getUserID(), 1, $page, config('page.order_list_page'));
            $list['history'] = $bnborder->getOrderListByUser($this->getUserID(), 0, $page, config('page.order_list_page'));

            $data['now'] = $this->fetch('order/sub/order', $list['now']);
            $data['history'] = $this->fetch('order/sub/order', $list['history']);


            $pagedata['now'] = $list['now']['page'];
            $pagedata['history'] = $list['history']['page'];

            $this->assign('data', $data);
            $this->assign('pagedata', $pagedata);
            $this->assign('title', '我的行程');


            return $this->fetch();
        } else {
            if ($now) {
                $list = $bnborder->getOrderListByUser($this->getUserID(), 1, $page, config('page.order_list_page'));
            } else {
                $list = $bnborder->getOrderListByUser($this->getUserID(), 0, $page, config('page.order_list_page'));
            }

            $data['data'] = $this->fetch('order/sub/order', $list);
            $data['page'] = $list['page'];

            return json($data);
        }
    }

    public function bnbOrder()
    {
        $params = Request::instance()->param();
        if (!$this->request->isAjax()) {
            if (!$params['bnb_id']) {
                return $this->error('未选择需要查看的房间');
            } else {
                $bnbInfo = (new Bnb())->getBnb($params['bnb_id']);
                $userInfo = session(config('session.UserInfo'));
                if ($bnbInfo['landlord_user'] != $userInfo['id']) {
                    return $this->error('您不是该民宿房东,无权限查看该民宿内容');
                }
            }
        }
        $page = $params['page'] ?? 1;
        $now = $params['now'] ?? 1;

        $bnborder = new BnbOrder();

        $list = [];

        if ($page == 1) {
            $list['now'] = $bnborder->getOrderListByBnb($params['bnb_id'], 1, $page, config('page.order_list_page'));
            $list['history'] = $bnborder->getOrderListByBnb($params['bnb_id'], 0, $page, config('page.order_list_page'));

            $data['now'] = $this->fetch('order/sub/order', $list['now']);
            $data['history'] = $this->fetch('order/sub/order', $list['history']);


            $pagedata['now'] = $list['now']['page'];
            $pagedata['history'] = $list['history']['page'];
            $this->assign('bnb_id', $params['bnb_id']);
            $this->assign('data', $data);
            $this->assign('pagedata', $pagedata);
            $this->assign('title', '订购记录');
            return $this->fetch('index');
        } else {
            if ($now) {
                $list = $bnborder->getOrderListByBnb($params['bnb_id'], 1, $page, config('page.order_list_page'));
            } else {
                $list = $bnborder->getOrderListByBnb($params['bnb_id'], 0, $page, config('page.order_list_page'));
            }

            $data['data'] = $this->fetch('order/sub/order', $list);
            $data['page'] = $list['page'];
            $data['bnb_id'] = $params['bnb_id'];

            return json($data);
        }
    }


    public function cancel()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {
            $e = (new BnbOrderLogic())->onCancel($order_sn, $this->getUserID(), 0);
            if ($e->checkResult()) {
                return $this->success("取消订单成功");
            } else {
                return $this->error("取消订单失败 " . $e->getText());
            }
        } else {
            return $this->error("未选择订单");
        }
    }

    public function delete()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {
            $e = (new BnbOrderLogic())->onDelete($order_sn, $this->getUserID(), 0);
            if ($e->checkResult()) {
                return $this->success("删除订单成功");
            } else {
                return $this->error("删除订单失败");
            }
        } else {
            return $this->error("未选择订单");
        }
    }


    public function paybnb()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {
            $e = (new BnbOrderLogic())->onPay($this->getWeChatApp(), $order_sn, $this->getWeChatID(), $this->getUserID(), 0);
            if ($e->checkResult()) {

                $jssdk = $e->getData();

                $url = [];
                $url['success'] = url('@index/Order/pay_status', ['order_sn' => $order_sn, 'result' => 1]);
                $url['fail'] = url('@index/Order/pay_status', ['order_sn' => $order_sn, 'result' => 0]);

                return $this->fetch("", ['jssdk' => $jssdk, 'url' => $url]);

            } else {
                return $this->error($e->getText());
            }
        } else {
            return $this->error("未选择订单");
        }
    }


    public function pay_status()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";
        $result = $params['result'] ?? false;

        if ($result) {
            $result = true;
        } else {
            $result = false;
        }

        $order = substr($order_sn, 0, 1);

        $url = "";
        if ($order == "B") {
            $url = url('@index/Order/detail', ['order_sn' => $order_sn]);
        } else {
            $url = url('@index/Order/detail', ['order_sn' => $order_sn]);
        }

        return $this->fetch("", ['order_sn' => $order_sn, 'status' => $result, 'url' => $url]);
    }


    public function detail()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";
        if ($order_sn) {

            $order = (new \app\common\model\OrderBnb())->getOrderBySn($order_sn);
            if ($order) {
                // 订单状态
                if ($order['status'] == BnbOrder::$OrderStatus_Delete) {
                    return $this->error("订单已经被删除");
                } else if ($order['user_id'] != $this->getUserID()) {
                    return $this->error("您无权查看此订单");
                } else {
                    $bnb = (new \app\common\model\Bnb())->getBnb($order['bnb_id']);

                    if ($bnb) {
                        $landload = (new Landlordinfo())->getLandloardByUID($bnb['landlord_user']);

                        $bnbdata['id'] = $bnb['id'];
                        $bnbdata['name'] = $bnb['name'];
                        $bnbdata['image'] = "/" . config('upload.upload')['thumb']['thumb1']['dir'] . '/' . $bnb['bnb_image'];
                        $bnbdata['traffic_content'] = $bnb['traffic_content'];
                        $bnbdata['attention_content'] = $bnb['attention_content'];
                        $bnbdata['fee_clean'] = $bnb['fee_clean'];

                        $bnb_logic = new BnbOrderLogic();

                        $bnbdata['operate'] = ($bnb_logic->getOperateList())[$order['status']]['user'];
                        $bnbdata['status'] = ($bnb_logic->getOperateList())[$order['status']]['order'];

                        //todo 开锁密码

                        if (isset($bnbdata['operate']['evaluate'])) {
                            if ($order['is_evaluate']) {
                                unset($bnbdata['operate']['evaluate']);
                            }
                        }

                        //按钮
                        $count = count($bnbdata['operate']);
                        if ($count > 0) {
                            $precent = 100 / $count;
                        } else {
                            $precent = 0;
                        }

                        $this->assign("order", $order);
                        $this->assign("bnb", $bnbdata);
                        $this->assign("landload", $landload);
                        $this->assign('title', '订单详情');

                        $this->assign('precent', $precent);
                        return $this->fetch();

                    } else {
                        return $this->error("民宿状态不正确");
                    }
                }
            } else {
                return $this->error("未找到订单");
            }
        } else {
            return $this->error("未找到订单");
        }
    }


    public function clean()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {
            $e = (new BnbOrderLogic())->onClean($order_sn, $this->getUserID(), 0);
            if ($e->checkResult()) {
                $order = $e->getData();
                $bnb = (new \app\common\model\Bnb())->getBnb($order['bnb_id']);

                if ($bnb) {

                    $bnbdata['id'] = $bnb['id'];
                    $bnbdata['name'] = $bnb['name'];
                    $bnbdata['image'] = "/" . config('upload.upload')['thumb']['thumb1']['dir'] . '/' . $bnb['bnb_image'];
                    $bnbdata['fee_clean'] = $bnb['fee_clean'];


                    $clean_numbers = [];
                    $clean_numbers['free'] = 0;
                    $clean_numbers['user'] = 0;

                    $clean_numbers['used'] = 0;
                    $clean_numbers['nouse'] = 0;
                    // 获取保洁次数
                    $order_clean = (new OrderAddonClean())->getUserOrderClean($order_sn);

                    $order_clean_list = [];
                    if ($order_clean) {
                        foreach ($order_clean as $oc) {
                            if ($oc['status'] == OrderAddonClean::$AddonCleanStatus_OK) {
                                $clean_numbers['nouse']++;
                            } else if (($oc['status'] == OrderAddonClean::$AddonCleanStatus_Order) || ($oc['status'] == OrderAddonClean::$AddonCleanStatus_Finish)) {
                                $clean_numbers['used']++;

                                $c = [];
                                $c['date'] = date("Y-m-d", $oc['clean_time']);
                                $c['state'] = "已预约";
                                if ($oc['status'] == OrderAddonClean::$AddonCleanStatus_Finish) {
                                    $c['state'] = "已完成";
                                }

                                $order_clean_list[] = $c;
                            }

                            if (floatval($oc['price']) != 0) {
                                $clean_numbers['user']++;
                            } else {
                                $clean_numbers['free']++;
                            }
                        }
                    }

                    // 保洁订单


                    $this->assign('order', $order);
                    $this->assign('bnb', $bnbdata);
                    $this->assign('cleannumbers', $clean_numbers);
                    $this->assign('cleanorder', $order_clean);

                    $this->assign('order_list', $order_clean_list);
                    if ($clean_numbers['nouse'] > 0) {
                        $precent = 50;
                    } else {
                        $precent = 100;
                    }

                    $this->assign('precent', $precent);
                    $this->assign('title', '评价订单');
                    return $this->fetch();

                } else {
                    return $this->error("无法获取民宿信息");
                }

            } else {
                return $this->error("无法获取保洁数据");
            }
        } else {
            return $this->error("未选择订单");
        }

    }


    public function evaluate()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {
            $e = (new BnbOrderLogic())->onEvaluate($order_sn, $this->getUserID(), 0);
            if ($e->checkResult()) {

                $order = $e->getData()['order'];

                $bnb = (new \app\common\model\Bnb())->getBnb($order['bnb_id']);

                if ($bnb) {

                    $bnbdata['id'] = $bnb['id'];
                    $bnbdata['name'] = $bnb['name'];
                    $bnbdata['image'] = "/" . config('upload.upload')['thumb']['thumb1']['dir'] . '/' . $bnb['bnb_image'];
                    $bnbdata['traffic_content'] = $bnb['traffic_content'];
                    $bnbdata['attention_content'] = $bnb['attention_content'];

                    $this->assign('order', $order);
                    $this->assign('bnb', $bnbdata);
                    $this->assign('title', '评价订单');
                    return $this->fetch();

                } else {
                    return $this->error("无法获取民宿信息");
                }

            } else {
                return $this->error("订单无法评价");
            }
        } else {
            return $this->error("未选择订单");
        }
    }


    public function doevaluate()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";

        if ($order_sn) {

            $photo_string = $params['images'] ?? "";

            $photo_array = [];
            if ($photo_string != "") {
                $photo_array = explode(',', $photo_string);
            }

            $score_array = $params['score'] ?? [];

            foreach ($score_array as $k => $v) {
                $n = intval($v);
                if ($n == 0) {
                    $n = 5;
                }
                $score_array[$k] = $n;
            }

            $evaluate_text = $params['evaluate'] ?? [];

            $e = (new BnbOrderLogic())->onDoEvaluate($order_sn, $this->getUserID(), 0, $score_array, $photo_array, $evaluate_text);
            if ($e->checkResult()) {

                return $this->success("评价订单成功");

            } else {
                return $this->error("订单无法评价");
            }
        } else {
            return $this->error("未选择订单");
        }
    }

    //调查问卷
    public function questionary()
    {
        $params = Request::instance()->param();
        $order_sn = $params['order_sn'] ?? "";
        if ($order_sn) {
            $order = (new \app\common\model\OrderBnb())->getOrderBySn($order_sn);
            if ($order) {
                // 订单状态
                if ($order['status'] == BnbOrder::$OrderStatus_Delete) {
                    return $this->error("订单已经被删除");
                } else if ($order['user_id'] != $this->getUserID()) {
                    return $this->error("您无权查看此订单");
                } else {
                    $bnb = (new \app\common\model\Bnb())->getBnb($order['bnb_id']);

                    if ($bnb) {
                        $clean_order = (new \app\common\model\OrderClean())->getLatestOrderByBnbId($order['bnb_id']);
                        $questionData = (new \app\common\model\Questionary())->getQuestionary();
                        $list = [
                            'bnb_order' => $order,
                            'clean_order' => $clean_order,
                            'questions' => $questionData
                        ];
                        $this->assign($list);
                        $this->assign('title', '调查问卷');
                        return $this->fetch();
                    } else {
                        return $this->error("民宿状态不正确");
                    }
                }
            } else {
                return $this->error("未找到订单");
            }
        } else {
            return $this->error("未找到订单");
        }
    }

    public function questionaryscore()
    {
        if (Request::instance()->isAjax()) {
            $params = Request::instance()->param();
            $score = [];
            $point_data = [];
            if (!isset($params['bnb_order_sn']) || !$params['bnb_order_sn']) {
                return $this->error('找不到民宿订单');
            }
            if (!isset($params['cleaner_id']) || !$params['cleaner_id']) {
                return $this->error('找不到保洁员');
            }
            if (!isset($params['clean_order_sn']) || !$params['clean_order_sn']) {
                return $this->error('找不到保洁订单');
            }
            if (!isset($params['bnb_id']) || !$params['bnb_id']) {
                return $this->error('找不到民宿信息');
            }
            if (!isset($params['question']) || !$params['question']) {
                return $this->error('无评分数据');
            }
            $scoreNum = (new OrderCleanScore())->getOrderCleanNumBySn($params['clean_order_sn'], $params['bnb_order_sn']);
            if ($scoreNum > 0) {
                return $this->error('该订单问卷调查已提交，请勿重复参评');
            }
            foreach ($params['q_id'] as $v) {
                $score[] = $params['score' . $v];
            }
            $score_total = array_sum($score);
            $score_full = array_sum($params['q_score']);
            $point = round(($score_total / $score_full), 2);
            $cleanPoint = 60;
            switch ($point) {
                case (config('message.qustionary_score')[60][0] <= $point && $point < config('message.qustionary_score')[60][1]):
                    $cleanPoint = 60;
                    break;
                case (config('message.qustionary_score')[70][0] <= $point && $point < config('message.qustionary_score')[70][1]):
                    $cleanPoint = 70;
                    break;
                case (config('message.qustionary_score')[80][0] <= $point && $point < config('message.qustionary_score')[80][1]):
                    $cleanPoint = 80;
                    break;
                case (config('message.qustionary_score')[90][0] <= $point && $point < config('message.qustionary_score')[90][1]):
                    $cleanPoint = 90;
                    break;
                case (config('message.qustionary_score')[100][0] <= $point && $point <= config('message.qustionary_score')[100][1]):
                    $cleanPoint = 100;
                    break;
            }
            $user_id = session(config('session.UserInfo'))['id'];
            foreach ($params['q_id'] as $v) {
                $point_data[] = [
                    'user_id' => $user_id,
                    'questionary_id' => $v,
                    'questionary_content_id' => $params['qc_id' . $v],
                    'score' => $params['score' . $v],
                    'clean_order_sn' => $params['clean_order_sn'],
                    'bnb_id' => $params['bnb_id'],
                    'cleaner_id' => $params['cleaner_id'],
                    'bnb_order_sn' => $params['bnb_order_sn'],
                    'score_total' => $cleanPoint
                ];
            }
            $result = (new OrderCleanScore())->saveAll($point_data);
            if ($result) {
                return $this->success('问卷调查已提交，感谢您的评价');
            } else {
                return $this->error('问卷调查提交失败');
            }

        } else {
            return $this->error('无效请求');
        }

    }

}