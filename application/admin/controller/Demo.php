<?php

namespace app\admin\controller;

use app\common\controller\Backend;

class Demo extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    //
    public function index()
    {
        return $this->view->fetch();
    }
}