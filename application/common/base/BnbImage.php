<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/25
 */

namespace app\common\base;

use app\common\model\ImageList;
use fast\Random;
use Intervention\Image\ImageManager;
use think\Request;

class BnbImage
{
    private $imageManager;
    private $image;

    public function __construct()
    {
    }


    public function loadImage($source, $type = "url")
    {

        if (!($this->imageManager)) {
            $this->imageManager = new ImageManager(array('driver' => 'GD'));
        }

        try {
            switch ($type) {
                case "upload":
                    $this->loadImageByUpload($source);
                    break;
                case "base64":
                    $this->loadImageByBase64($source);
                    break;
                default:
                    $this->loadImageCommon($source);
                    break;
            }

            $this->image->backup();

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }


    public function showImageUploaderHtml($upload_type, $field,$ext=null,$multiple=false)
    {
        $data_mime = [];
        $data_url = "";
        $data_maxsize = "";
        $data_multiple = $multiple;

        $mimelist = config('mime.image');
        $typelist = config('upload.imagetype');
        $savedb = config('upload.default_savedb');

        $uploadconfig = null;
        if (in_array($upload_type, $typelist)) {
            $uploadconfig = config('upload.' . $upload_type);

            if ($uploadconfig) {

                if (isset($uploadconfig['mimetype'])) {

                    foreach ($uploadconfig['mimetype'] as $mt) {
                        if ($mimelist[$mt]) {
                            $data_mime[$mimelist[$mt]] = 1;
                        }
                    }
                }

                if (isset($uploadconfig['maxsize'])) {
                    $data_maxsize = $uploadconfig['maxsize'];
                } else {
                    $data_maxsize = config('upload.default_maxsize');
                }

                if (isset($uploadconfig['uploader'])) {
                    $data_url = url($uploadconfig['uploader'], ['type' => $upload_type,'ext'=>$ext]);
                } else {
                    $data_url = url(config('upload.default_uploader'), ['type' => $upload_type,'ext'=>$ext]);
                }

                if (isset($uploadconfig['multiple'])) {
                    $data_multiple = $uploadconfig['multiple'];
                } else {
                    $data_multiple = config('upload.default_multiple');
                }


                $outhtml = "";

                if (($data_mime) && (is_array($data_mime))) {
                    $mi = array_keys($data_mime);
                    $dm = implode(",", $mi);
                    if ($dm) {
                        $outhtml .= " data-mimetype=\"" . $dm . "\" ";
                    }
                }

                if (($data_multiple) || ($multiple)) {
                    $outhtml .= " data-multiple=\"true\" ";
                } else {
                    $outhtml .= " data-multiple=\"false\" ";
                }

                $outhtml .= " data-url=\"" . $data_url . "\" ";
                $outhtml .= " data-maxsize=\"" . $data_maxsize . "\" ";

                if (isset($uploadconfig['uploader_callback'])) {
                    $outhtml .= " data-upload-success=\"" . $uploadconfig['uploader_callback'] . "\" ";
                }


                $preview_dir = $this->getImageTypePreviewDir($upload_type);

                if ($preview_dir) {
                    $outhtml .= " data-preview=\"/" . $preview_dir . "\" ";
                }


                $imagebtn_string = '<span><button type="button" id="plupload-'.$field.'" class="btn btn-danger plupload" data-input-id="c-'.$field.'" data-preview-id="p-'.$field.'" '.$outhtml.'><i class="fa fa-upload"></i> 上传</button></span>';



                $typedb = isset($config["savedb"]) ?: null;


                if ($typedb) {
                    $savedb = $typedb;
                }

                $multiple_string = "false";
                if ($data_multiple) {
                    $multiple_string = "true";
                }

                if ($savedb) {
                    // show choose

                    $imagebtn_string .= '<span><button type="button" id="bnbchooseimage-' . $upload_type . '" class="btn btn-primary bnbchooseimage" data-input-id="c-' . $field . '" data-multiple="' . $multiple_string . '" data-imagetype="' . $upload_type . '"><i class="fa fa-list"></i> 选择</button></span>';
                }


                return $imagebtn_string;
            }

        }
        return '';
    }

    public function loadImageByUpload($field = "file")
    {
        $this->image = $this->imageManager->make(Request::instance()->file($field));
    }

    public function loadImageCommon($url)
    {
        $this->image = $this->imageManager->make($url);
    }

    public function loadImageByBase64($base64)
    {
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result);
        $imagecontent = base64_decode(str_replace($result[1], '', $base64));
        if ($imagecontent) {
            $this->image = $this->imageManager->make($imagecontent);
        } else {
            throw new \Exception("base 64 no content");
        }
    }


    public function ImageSize($width, $height, $color = "#000000")
    {
        $newimage = null;
        $newimage = $this->imageManager->canvas($width, $height, $color);

        $this->image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $newimage->insert($this->image->stream(), 'center');

        return $newimage;
    }

    public function watermark($watermark_file, $img = null)
    {
        $watermark_file = ROOT_PATH . "/public/" . $watermark_file;

        if (!$img) {
            $img = $this->image;
        }
        if (is_file($watermark_file)) {
            if ($img) {
                $img->insert($watermark_file, 'bottom-right', 10, 10);
            } else {
                $this->image->insert($watermark_file, 'bottom-right', 10, 10);
            }
        }
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getMd5($img = null)
    {
        if (!$img) {
            $img = $this->image;
        }
        if ($img) {
            return md5($img->stream());
        } else {
            return "";
        }
    }

    public function getSHA1($img = null)
    {
        if (!$img) {
            $img = $this->image;
        }
        if ($img) {
            return sha1($img->stream());
        } else {
            return "";
        }
    }

    public function getSuffix($img = null)
    {
        if (!$img) {
            $img = $this->image;
        }
        if ($img) {
            return $this->getImageSuffix($img->mime());
        } else {
            return "";
        }
    }

    public function save($filetype = "upload", $ext = null, $saverule = "md5", $savedb = true)
    {
        $thumb_name = [];
        $image_name = "";

        $config = $this->getImageDirByType($filetype);

        if ($config) {

            // 保存最终文件名
            $filemd5 = $this->getMd5();
            $filesuffix = $this->getSuffix();
            $imagesavefile = $config['savefile'];
            $replaceArr = $this->fileNameArray($filesuffix, $filemd5, $ext);
            $image_file = str_replace(array_keys($replaceArr), array_values($replaceArr), $imagesavefile);

            if ($config['go_thumb']) {
                // 生成缩略图
                $thumb_config = $config['thumb'];
                if (($thumb_config) && (is_array($thumb_config))) {
                    foreach ($thumb_config as $tc) {

                        $width = $tc['width'];
                        $height = $tc['height'];
                        $savefile = ROOT_PATH . "/public/" . $tc['dir'] . "/" . $image_file;

                        $thumb = $this->ImageSize($width, $height);

                        if ($tc['watermark']) {
                            $this->watermark($tc['watermark_file'], $thumb);
                        }

                        if ($this->createPath($savefile)) {
                            $thumb->save($savefile);
                            $thumb_name[] = "/" . $tc['dir'] . "/" . $image_file;
                        }

                        $this->image->reset();
                    }
                }
            }

            if ($config['go_image']) {

                $image_config = $config['image'];

                // 生成原图
                $width = $image_config['max_width'];
                $height = $image_config['max_height'];
                $savefile = ROOT_PATH . "/public/" . $image_config['dir'] . "/" . $image_file;

                $img_width = $this->image->width();
                $img_height = $this->image->height();

                if ((($img_width > $width) && ($width > 0)) || (($img_height > $height) && ($height > 0))) {
                    // 超过了最大大小，调整图片
                    $this->image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                if ($image_config['watermark']) {
                    $this->watermark($image_config['watermark_file']);
                }

                if ($this->createPath($savefile)) {
                    $this->image->save($savefile);
                    $image_name = "/" . $image_config['dir'] . "/" . $image_file;
                }
            }


            // 保存入数据库
            $savedb = config('upload.default_savedb');
            if (isset($config['savedb'])) {
                $savedb = $config['savedb'];
            }
            if ($savedb) {

                $db_imagelist = new ImageList();

                $exist = $db_imagelist->getImageByMd5($filemd5, $filetype);

                if ($exist) {
                    $db_imagelist->save(['updatetime' => time()], ['id' => $exist['id']]);
                } else {
                    $param = [
                        'imagetype' => $filetype,
                        'file' => $image_file,
                        'md5' => $filemd5,
                        'mime' => $filesuffix,
                        'storage' => 'local',
                    ];
                    $db_imagelist->create($param);
                }
            }

            return [
                'thumb' => $thumb_name,
                'image' => $image_name,
                'file' => $image_file,
            ];
        } else {
            return null;
        }
    }


    public function getImageDirByType($filetype)
    {
        $config = config('upload.imagetype');
        if (in_array($filetype, $config)) {
            return config('upload.' . $filetype);
        }
        return null;
    }


    public function fileNameArray($suffix, $md5, $ext = null)
    {
        $extarray = [];

        if (($ext) && (is_array($ext))) {
            foreach ($ext as $k => $v) {
                $nk = '{ext' . ($k + 1) . '}';
                $extarray[$nk] = $v;
            }
        }
        $filearray = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => $md5,
            '{filemd5_2}' => substr($md5, 0, 2),
            '{filemd5_30}' => substr($md5, 2, 30),
        ];

        if ($extarray) {
            $filearray = array_merge($extarray, $filearray);
        }
        return $filearray;
    }

    public function getImageSuffix($type)
    {
        $suf = config('mime.image');
        $suf = array_flip($suf);

        if ($suf[$type]) {
            return $suf[$type];
        } else {
            return '';
        }
    }

    public function createPath($filename)
    {
        $path = dirname($filename);

        if (is_dir($path)) {
            return true;
        }

        if (mkdir($path, 0755, true)) {
            return true;
        } else {
            return false;
        }

    }


    public function getImageTypePreviewDir($imagetype)
    {

        $preview_dir = "";

        $uploadconfig = config('upload.' . $imagetype);

        if (isset($uploadconfig['preview'])) {
            $preview_string = $uploadconfig['preview'];
            $preview = explode(".", $preview_string);

            $c = $uploadconfig;
            foreach ($preview as $p) {
                if (isset($c[$p])) {
                    $c = $c[$p];
                } else {
                    $c = null;
                    break;
                }
            }

            if (isset($c['dir'])) {
                $preview_dir = $c['dir'];
            }
        }

        return $preview_dir;
    }
}