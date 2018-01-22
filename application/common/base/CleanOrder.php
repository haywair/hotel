<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/11/13
 */

namespace app\common\base;

use app\common\model\OrderAddonClean;
use app\common\model\OrderClean;
use app\common\model\BillClean;
use app\common\model\Cleaninfo;
use app\common\model\OrderCleanPhoto;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use think\Exception;

class CleanOrder
{
    /**
     *  自动为等待保洁的订单分配空闲的保洁员， 分配依据（最近 -> 空闲）
     *
     */
    public function allocWaitingCleanerOrder()
    {
        $now = time();

        // 根据当前时间，获取不同的订单列表
        $nowhour = date('H', $now);
        $day = 1;
        if (($nowhour >= config('setting.clean_order_begin_hour')) && ($nowhour <= config('setting.clean_order_end_hour'))) {
            $day = config('setting.clean_order_auto_day');
        }
        $clean_start_time = $now + $day * 24 * 3600;

        $orderlist = (new OrderClean())->getWaitingOrderList($clean_start_time);
        if ($orderlist) {
            $ac = new AutoCleaner();

            foreach ($orderlist as $ol) {
                $cleaner_id = $ac->getCleaner($ol['map_lng'], $ol['map_lat'], date('Y-m-d', $ol['clean_start_time']));
                if ($cleaner_id) {
                    $this->allocCleanOrder($ol, $cleaner_id);
                }
            }

        }
    }

    public function allocCleanOrder($cleanorder, $cleaner_id)
    {
        $return = false;

        $model_orderclean = new OrderClean();
        try {
            $model_orderclean->startTrans();

            $r = $model_orderclean->saveCleanerToOrder($cleanorder['id'], $cleaner_id);
            if (!$r) {
                throw new Exception("保存保洁员数据到订单失败", 550);
            }

            $r = (new OrderAddonClean())->updateOrderCleanerId($cleanorder['order_sn'], $cleaner_id);
            if (!$r) {
                throw new Exception("保存保洁员数据到订单失败", 551);
            }

            $model_orderclean->commit();

            $return = true;
        } catch (Exception $e) {
            $model_orderclean->rollback();
        }

        return $return;
    }

    /**
     * 比较上传的图片的相似度
     *
     * @param $compare_id
     * @param $order_clean_id
     * @param $image
     * @param bool $needadmin
     * @return bool|int
     */
    public function compareCleanPhoto($compare_id, $order_clean_id, $image, $needadmin = false)
    {
        $upload = config('upload');

        $org_file = "";
        $new_file = "";


        $org_image = (new OrderCleanPhoto())->getCleanPhoto($compare_id, $order_clean_id);
        if ($org_image) {
            $org_file = ROOT_PATH . "public/" . $upload['bnb_clean_photo']['thumb']['thumb']['dir'] . "/" . $org_image['image'];
            $new_file = ROOT_PATH . "public/" . $upload['upload_clean_photo']['thumb']['thumb']['dir'] . "/" . $image;
        }


        if (($org_file) && ($new_file)) {
            if ((is_file($org_file)) && (is_file($new_file))) {

                // 图片对比
                $diff_image = new DifferenceHash();
                $hasher = new ImageHash($diff_image);
                $distance = $hasher->compare($org_file, $new_file);
                if ($distance === 0) {
                    $distance = 1;
                }
                return $distance;
            }
        }

        return false;
    }

    /**
     * 保洁员标记保洁订单完成
     *
     * @param $order_clean_id
     * @param $cleaner_id
     * @return Error
     *
     */
    public function makeCleanOrderFinish($order_clean_id, $cleaner_id)
    {
        $error = new Error();
        try {
            $model_order_clean = new OrderClean();

            $order = $model_order_clean->getOrderById($order_clean_id);
            if ($order) {
                //检查订单状态
                if (($order['status'] != OrderClean::$BnbCleanStatus_ToCleaner) && ($order['status'] != OrderClean::$BnbCleanStatus_DoCleaner)) {
                    throw new Exception("订单状态不正确", 500);
                }

                if ($order['cleaner_id'] != $cleaner_id) {
                    throw new Exception("您没有权限修改此订单", 500);
                }

                if ($order['photo_compare']) {
                    // 需要提交比较照片
                    $photolist = (new OrderCleanPhoto())->getOrderPhotoList($order_clean_id);
                    if ($photolist) {
                        foreach ($photolist as $p) {

                            if (($p['upload_time'] > 0) && ($p['upload_image'])) {
                                if (($p['compare_value'] > 0) && ($p['compare_value'] <= config('setting.clean_photo_distince'))) {
                                    // compare pass
                                } else {

                                    if ($p['need_admin']) {

                                    } else {
                                        throw new Exception("请确保所有图片都通过后，再提交完成。如果多次上传图片都不能成功，请选择人工审核图片", 500);
                                    }

                                }
                            } else {
                                throw new Exception("请先上传所有保洁后图片，再提交完成", 500);
                            }
                        }
                    }
                }
            } else {
                throw new Exception("获取保洁订单数据失败", 500);
            }

            // 校验数据完成

            $r = $model_order_clean->updateCleanOrderFinish($order_clean_id);
            if ($r) {
                $error->setOk([]);
                (new OrderAddonClean())->updateOrderFinish($order['order_sn']);
            } else {
                throw new Exception("更新保洁订单数据失败", 500);
            }

        } catch (Exception $e) {

            $error->setError($e->getCode(), $e->getMessage());
        }

        return $error;
    }
    /**
     *  自动审核完成保洁订单
     *
     */
    public function finishCleanerOrder()
    {
        $now = time();

        // 根据当前时间，获取不同的订单列表
        $timestamp = $now;
        //$timestamp = time() - 3600*24*config('setting.clean_order_auto_bill_day');
        $orderlist = (new OrderClean())->getWaitingBillOrderList($timestamp);
        if ($orderlist) {
            $ac = new AutoCleaner();
            foreach ($orderlist as $ol) {
                //获取结算金额
                $billData = $ac->reckonCleanOrderBill($ol);
                if ($billData) {
                    $clean_order_id = $billData['order_clean_id'];
                    $fee_cleaner = $billData['fee_cleaner'];
                    $verify_score = $billData['verify_score'];
                    $cleaner_id = $billData['cleaner_id'];
                    $bnb_id = $billData['bnb_id'];
                    $result = $this->dealCleanerOrder($clean_order_id,$fee_cleaner,$verify_score,$cleaner_id,$bnb_id);
                }
            }
        }
    }

    public function dealCleanerOrder($clean_order_id,$fee_cleaner,$verify_score,$cleaner_id,$bnb_id)
    {
        $model_order_clean = new OrderClean();
        $model_bill_clean = new BillClean();
        $model_clean = new Cleaninfo();
        $return = false;
        try {
            $model_order_clean->startTrans();
            $order = $model_order_clean->getOrderById($clean_order_id);
            if ($order) {
                //检查订单状态
                if (($order['status'] != OrderClean::$BnbCleanStatus_FinishCleaner)) {
                    throw new Exception("订单状态不正确", 500);
                }
            } else {
                throw new Exception("获取保洁订单数据失败", 500);
            }
            // 校验数据完成

            $r = $model_order_clean->updateCleanOrderVerify($clean_order_id,$fee_cleaner,$verify_score);
            if ($r) {
                //添加结算数据
                $billData = [
                    'bill_date'     =>  date('Y-m-d'),
                    'bnb_id'        =>  $bnb_id,
                    'clean_order_id'    =>  $clean_order_id,
                    'clean_money'       =>  $fee_cleaner,
                    'cleaner_id'        =>  $cleaner_id
                ];
                $model_bill_clean->save($billData);
                //修改保洁员信息
                $cleaninfo = $model_clean->getCleanerByUID($cleaner_id);
                if($cleaninfo) {
                    $cleaner_money_total = $cleaninfo['money_total'] + $fee_cleaner;
                    $model_clean->updateCleaninfoByUId($cleaner_id, ['money_total' => $cleaner_money_total]);
                }else{
                    throw new Exception("获取保洁员数据失败", 600);
                }

            } else {
                throw new Exception("更新保洁订单数据失败", 500);
            }
            $model_order_clean->commit();
            $return = true;

        } catch (Exception $e) {
            $model_order_clean->rollback();
        }

        return $return;
    }

}