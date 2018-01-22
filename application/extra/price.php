<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14 0014
 * Time: 13:47
 */
return [

    'search_price_list' => [
        ['name' => '价格不限', 'price' => [0, 0], 'default' => 1],
        ['name' => '500元以下', 'price' => [0, 500], 'default' => 0],
        ['name' => '500-1000元', 'price' => [500, 1000], 'default' => 0],
        ['name' => '1000元以上', 'price' => [1000, 0], 'default' => 0],
    ]
];