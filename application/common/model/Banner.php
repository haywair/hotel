<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13 0013
 * Time: 10:37
 */

namespace app\common\model;

use think\Model;

class Banner extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 轮播图列表
     * @param array $condition
     * @param string $order
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBannerList($condition = [], $order = 'weigh desc,id desc', $limit = 4)
    {

        $data = [];

        if (!$condition) {
            $condition = [
                'status' => ['gt', config('state.state_disable')]
            ];
        }
        $list = $this->where($condition)->order($order)->limit($limit)->select();

        if (($list) && (is_array($list))) {

            $banner_path = '/' . config('upload.banner')['image']['dir'] . '/';
            foreach ($list as $item) {

                if ($item['image']) {
                    $b = [];
                    $b['name'] = $item['name'];
                    $b['image'] = $banner_path . $item['image'];
                    $b['url'] = $item['url'];

                    $data[] = $b;
                }
            }
        }

        return $data;
    }
}