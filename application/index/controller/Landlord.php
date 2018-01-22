<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16 0016
 * Time: 8:56
 */

namespace app\index\controller;
use app\common\base\BnbClean;
use app\common\base\BnbOrder;
use app\common\base\BnbPaid;
use app\common\base\BnbPay;
use app\common\base\BnbPrice;
use app\common\base\UserCleanOrder;
use app\common\base\Image;
use app\common\controller\BnbBase;
use app\index\validate\Signup;
use think\Request;
class Landlord extends BnbBase
{
    public function _initialize()
    {
        parent::_initialize();
        $this->users = session(config('session.UserInfo'));
        $this->bnbModel = new \app\common\model\Bnb();
        $this->signupModel = new \app\common\model\Signup();
        $this->userModel = new \app\common\model\Users();
        $this->imagePath = [
            'bnb_path'  => '/'.config('upload.upload')['thumb']['thumb1']['dir'].'/',
            'bnb_thumb_path' => '/'.config('upload.upload')['thumb']['thumb2']['dir'].'/',
            'avatar_path' => '/'.config('upload.avatar')['thumb']['avatar']['dir'].'/'
        ];
    }

    /**
     * 房东民宿列表
     */
    public function index(){
        $condition = [
            'a.users_id' => $this->users['id'],
            'a.type' => config('users.landlord_type'),
            'a.status'  => config('state.state_ok')
        ];
        $signupData = $this->signupModel->getSingupPageList($condition);
        if($signupData) {
            foreach ($signupData as $k => $v) {
                $name = '未填写';//申请的民宿名称
                $img = '';//申请民宿的图片
                if ($v['info']) {
                    $signupInfo = json_decode($v['info'],true);
                    $name = isset($signupInfo['title']) ? $signupInfo['title'] : '未填写';
                    $img = isset($signupInfo['bnb_img']) ? $signupInfo['bnb_img'] : '';
                }
                $signupData[$k]['name'] = $name;
                $signupData[$k]['bnb_img'] = $img;
            }
        }
        $whereBnb = [
            'a.landlord_user' => $this->users['id'],
            'a.status'    => array('IN',[config('state.state_ok'),config('state.state_disable'),config('state.state_mark')])
        ];
        $bnbData = $this->bnbModel->getLandlordBnbList($whereBnb);
        $this->assign('bnbData',$bnbData);
        $this->assign('stateList',config('state.state'));
        $this->assign('signupData',$signupData);
        $this->assign('path',$this->imagePath);
        $this->assign('title', '我是房东');
        return $this->fetch();

    }
    /**
     * 注册成为房东
     * @return mixed
     */
    public function registerLandlord(){
        if($this->request->isPost()){
            $params = $this->request->request();
            $validate = new Signup();
            if(!$validate->check($params)){
                $this->error($validate->getError());
            }else{
                //用户个人信息
                $users = session(config('session.UserInfo'));
                $userInfo = $this->userModel->get($users['id']);
                $params['users_id'] = $users['id'];
                $params['age'] = $userInfo['user_age'];
                $params['type'] = config('users.landlord_type');
                $params['sex'] = $userInfo['user_sex'];

                $result = $this->signupModel->save($params);
                if($result){
                    $this->success('恭喜您，成功注册为房东');
                }
                $this->error('注册房东失败');
            }
        }else{
            $this->assign('title', '成为房东');
            return $this->fetch();
        }
        return null;
    }

    /**
     * 申请添加民宿
     */
    public function addBnb($type=null){
        //session读取设置数据
        $typeKeys = config('session.Landlord_bnb_type');
        $addData = [];
        foreach ($typeKeys as $k => $v) {
            $addData[$v] = session($v);
        }

        if($this->request->isPost()){
            $bnb_img = $this->request->request('bnb_img');
            if(!$bnb_img && !$addData){
                $this->error('请上传您所需要提交的资料');
            }
            $addData['bnb_img'] = $bnb_img;
            $userInfo = $this->userModel->getUserById($this->users['id']);
            $data = [
                'users_id'          => $this->users['id'],
                'contact_mobile'    => $userInfo['user_mobile'],
                'type'              => config('users.landlord_type'),
                'street'            => $addData['address']?$addData['address']:'',
                'info'              => json_encode($addData)
            ];
            $res = $this->signupModel->save($data);
            if($res){
                $this->success('提交成功');
            }
            $this->error('提交失败');
        }else {
            if($type){
                $this->assign('addData',$addData);
                $this->assign('type',$type);
            }
            $this->assign('path',$this->imagePath);
            $this->assign('typeKeys',$typeKeys);
            $this->assign('title', '添加民宿');
            return $this->fetch();
        }

    }
    /**
     * 添加民宿内容
     */
    public function addStepOne($type=null){
        if($this->request->isPost()){
            $keys = $this->request->request('keys');
            $val = $this->request->request('val');
            if($keys && $val){
                if(session($keys)){
                    session($keys,null);
                }
                session($keys,$val);
                $this->success('设置成功');
            }else{
                $this->error('传入参数不完整');
            }

        }else {
            if ($type) {
                $text = '';//input placeholder提示
                $nameVal = '';//input name id属性值
                $template = '';//模板
                switch ($type) {
                    case config('session.Landlord_bnb_type')['title']:
                        $text = '标题';
                        $nameVal = 'title';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['demo_content']:
                        $text = '房源介绍';
                        $nameVal = 'demo_content';
                        $template = 'addStepTwo';
                        break;
                    case config('session.Landlord_bnb_type')['address']:
                        $text = '地址';
                        $nameVal = 'address';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['room']:
                        $text = '房源类型';
                        $nameVal = 'room';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['bed']:
                        $text = '床型';
                        $nameVal = 'bed';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['people']:
                        $text = '人数';
                        $nameVal = 'people';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['price']:
                        $text = '价格';
                        $nameVal = 'price';
                        $template = 'addStepOne';
                        break;
                    case config('session.Landlord_bnb_type')['features']:
                        $text = '设施';
                        $nameVal = 'features';
                        $template = 'addFeatures';
                        break;
                    case config('session.Landlord_bnb_type')['rules']:
                        $text = '入住规则';
                        $nameVal = 'rules';
                        $template = 'addStepTwo';
                        break;
                }
                $typeKeys = config('session.Landlord_bnb_type');
                $this->assign('text',$text);
                $this->assign('nameVal',$nameVal);
                $this->assign('typeKeys', $typeKeys);
                $this->assign('title', '添加'.$text);
                return $this->fetch($template);
            }
            $this->error('未指定添加内容信息');
        }
    }
    /**
     * 设置房源状态
     */
    public function setBnbState(){
        if($this->request->isAjax()){
            $params = $this->request->post();
            if(!$params['id']){
                $this->error('未提供民宿信息');
            }
            $status = $params['status']?$params['status']:0;
            $res = $this->bnbModel->updateBnbByBID($params['id'],['status'=>$params['status']]);
            if($res){
                $this->success('设置成功');
            }else{
                $this->success('设置失败');
            }
        }else{
            $this->error('无效请求');
        }
    }



}