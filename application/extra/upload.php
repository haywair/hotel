<?php

//上传配置
return [
    /**
     * 上传地址,默认是本地上传
     */
    'uploadurl' => 'ajax/upload',
    /**
     * CDN地址
     */
    'cdnurl' => '',
    /**
     * 文件保存格式
     */
    'savekey' => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
    /**
     * 最大可上传大小
     */
    'maxsize' => '10mb',
    /**
     * 可上传的文件类型
     */
    'mimetype' => '*',
    /**
     * 是否支持批量上传
     */
    'multiple' => false,


    //////////////////////////////

    // 默认上传接口
    'default_uploader' => 'admin/ImageUpload/index',
    'default_maxsize' => 20 * 1024 * 1024, //20m
    'default_multiple' => false,
    'default_savedb' => true, // 是否存入数据库

    // 图像处理分类
    'imagetype' => [
        'avatar',
        'upload',
        'bnb_clean_photo',
        'upload_clean_photo',
        'banner',
        'evaluate',
        'idcard'
    ],


    'avatar' => [   //用户头像信息

        'go_thumb' => true,     // 缩略图当作最终头像
        'go_image' => false,    //不要原图
        'savefile' => '{ext1}_{filemd5}.png',// 文件保存规则
        'preview' => 'thumb.avatar', //返回预览图
        'savedb' => false,

        'thumb' => [ // 缩略图
            'avatar' => [ // 第一种类型
                "dir" => 'Avatar',
                'width' => 160,
                'height' => 160,
                'watermark' => false,
                'watermark_file' => '',
            ],

        ],
    ],
    'idcard' => [   //用户头像信息

        'go_thumb' => true,     // 缩略图当作最终头像
        'go_image' => false,    //不要原图
        'savefile' => '{ext1}_{filemd5}.png',// 文件保存规则
        'preview' => 'thumb.idcard', //返回预览图
        'savedb' => false,

        'thumb' => [ // 缩略图
            'idcard' => [ // 第一种类型
                "dir" => 'Idcard',
                'width' => 320,
                'height' => 240,
                'watermark' => false,
                'watermark_file' => '',
            ],

        ],
    ],
    'banner' => [   //用户头像信息

        'go_thumb' => true,     // 缩略图当作最终头像
        'go_image' => true,    //不要原图
        'savefile' => '{filemd5_2}/{filemd5_30}.{suffix}',// 文件保存规则
        'preview' => 'thumb.thumb1', //返回预览图
        'savedb' => false,

        'thumb' => [ // 缩略图

            'thumb1' => [ // 第一种类型

                "dir" => 'Thumb_banner',
                'width' => 640,
                'height' => 480,
                'watermark' => true,
                'watermark_file' => '/images/mask.png',
            ],
        ],

        'image' => [ // 原图
            "dir" => 'Banner',
            'max_width' => 640,
            'max_height' => 360,
            'watermark' => true,
            'watermark_file' => '/images/mask.png',
        ],

        'uploader' => 'admin/ImageUpload/index',
        'maxsize' => 20 * 1024 * 1024, //20m
        'mimetype' => [
            'jpg',
            'gif',
            'jpeg',
            'png'
        ],
    ],


    'upload' => [   //普通图片信息

        'go_thumb' => true, //保存缩略图
        'go_image' => true, //保存原图
        'savefile' => '{filemd5_2}/{filemd5_30}.{suffix}', // 文件保存规则
        'preview' => 'thumb.thumb2',
        'multiple' => false,
        'savedb' => true,

        'thumb' => [ // 缩略图

            'thumb1' => [ // 第一种类型

                "dir" => 'Thumb',
                'width' => 640,
                'height' => 480,
                'watermark' => true,
                'watermark_file' => '/images/mask.png',
            ],

            'thumb2' => [ // 第二种类型

                "dir" => 'Thumb2',
                'width' => 320,
                'height' => 240,
                'watermark' => true,
                'watermark_file' => '/images/mask.png',
            ],
        ],


        'image' => [ // 原图
            "dir" => 'BNB',
            'max_width' => 1600,
            'max_height' => 1200,
            'watermark' => true,
            'watermark_file' => '/images/mask.png',
        ],

        'uploader' => 'admin/ImageUpload/index',
        'maxsize' => 20 * 1024 * 1024, //20m
        'mimetype' => [
            'jpg',
            'gif',
            'jpeg',
            'png'
        ],

    ],

    'bnb_clean_photo' =>[   //民宿保洁对比图片 4:3图片

        'go_thumb' => true, //保存缩略图
        'go_image' => false, //保存原图

        'savefile' => '{filemd5_2}/{filemd5_30}.{suffix}', // 文件保存规则
        'preview' => 'thumb.thumb',
        'multiple' => false,
        'savedb' => false,

        'thumb' => [ // 缩略图
            'thumb' => [ // 第一种类型
                "dir" => 'Compare/BnbClean',
                'width' => 640,
                'height' => 480,
                'watermark' => false,
                'watermark_file' => '',
            ],
        ],

        'uploader' => 'admin/ImageUpload/index',
        'maxsize' => 5 * 1024 * 1024, //20m
        'mimetype' => [
            'jpg',
            'gif',
            'jpeg',
            'png'
        ],
    ],


    'upload_clean_photo' =>[   //民宿保洁对比图片 4:3图片

        'go_thumb' => true, //保存缩略图
        'go_image' => false, //保存原图

        'savefile' => '{filemd5_2}/{filemd5_30}.{suffix}', // 文件保存规则
        'preview' => 'thumb.thumb',
        'multiple' => false,
        'savedb' => false,

        'thumb' => [ // 缩略图
            'thumb' => [ // 第一种类型
                "dir" => 'Compare/UploadClean',
                'width' => 640,
                'height' => 480,
                'watermark' => false,
                'watermark_file' => '',
            ],
        ],

        'uploader' => 'admin/ImageUpload/index',
        'maxsize' => 5 * 1024 * 1024, //20m
        'mimetype' => [
            'jpg',
            'gif',
            'jpeg',
            'png'
        ],
    ],


    'evaluate' => [   //用户评价图片
        'go_thumb' => true,     // 缩略图当作最终头像
        'go_image' => false,    //不要原图
        'savefile' => '{filemd5_2}/{filemd5_30}.{suffix}',// 文件保存规则
        'preview' => 'thumb.thumb', //返回预览图
        'savedb' => false,

        'thumb' => [ // 缩略图
            'thumb' => [ // 第一种类型
                "dir" => 'Evaluate',
                'width' => 320,
                'height' => 320,
                'watermark' => false,
                'watermark_file' => '',
            ],

        ],
    ],
];
