<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2018/1/20
 */

namespace app\common\model;

use think\Model;

class BnbLockEvent extends Model
{

    public function addEvent($event)
    {
        if ($event != "") {
            $data = [];
            $data['createtime'] = time();
            $data['json'] = $event;
            $this->insert($data);
        }
    }
}