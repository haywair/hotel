<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use think\Exception;

/**
 * 系统配置
 *
 * @icon fa fa-circle-o
 */
class Siteconfig extends Backend
{

    protected $model = null;
    protected $noNeedRight = [];

    public function _initialize()
    {
        parent::_initialize();
    }
    public function index()
    {
        $this->assign('config_list',config('site.config_list'));
        return $this->view->fetch();
    }
}
