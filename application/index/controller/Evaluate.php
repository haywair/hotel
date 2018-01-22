<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14 0014
 * Time: 10:33
 */

namespace app\index\controller;

use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\model\BnbInfo;
use think\Db;
use app\common\controller\BnbBase;
class Evaluate extends BnbBase
{
    protected $bnbModel;
    protected $featureModel;
    public function _initialize()
    {

        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->bnbModel = new \app\common\model\Bnb();
        $this->imageModel = new \app\common\model\Images();
        $this->landlordModel = new \app\common\model\Landlordinfo();
        $this->orderModel = new \app\common\model\OrderBnb();
        $this->evaluateModel = new \app\common\model\Evaluate();
        $this->evaluatePhotoModel = new \app\common\model\EvaluatePhoto();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/',
            'evaluate_path' => '/'.config('upload.evaluate')['thumb']['thumb']['dir'].'/'
        ];
    }

    /**
     * 民宿房间评价列表
     * @param null $bnbId
     */
    public function index($bnbId = null){

        $condition = [
            'a.status' => array('in',config('state.state_ok').','.config('state.state_mark')),
        ];
        if($bnbId){
            $condition['a.bnb_id'] = $bnbId;
        }
        $data = $this->evaluateModel->getEvaluteList($condition);
        if($data){
            foreach ($data as $k => $v) {
                if ($v['photos'] > 0) {
                    $data[$k]['images'] = $this->evaluatePhotoModel->getEvalutePhotosBnb($v['id'], $v['bnb_id']);
                }
            }
        }
        $this->assign('bnbId',$bnbId);
        $this->assign('data',$data);
        $this->assign('title','评价');
        $this->assign('path',$this->imagePath);
        return $this->fetch();

    }

}