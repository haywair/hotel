<?php
/**
 * Created by Wang.Gang@SDTY
 * Mailto glogger#gmail.com
 * 2017/10/31
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\ImageList;
use think\Request;
use app\common\base\BnbImage;

class ImageSelect extends Backend
{

    /**
     * 图片选择
     * @return string|\think\response\Json
     */
    public function index()
    {

        $request = Request::instance();

        if ($request->isAjax()) {

            $order = $request->get("order", "DESC");
            $offset = $request->get("offset", 0);
            $limit = $request->get("limit", 0);
            $imagetype = $request->get("imagetype", "upload");
            $sort = $request->get("sort", "updatetime");

            $data = (new ImageList())->getImageListByType($imagetype, $offset, $limit, $sort, $order);
            $previewdir = (new BnbImage())->getImageTypePreviewDir($imagetype);
            if ($previewdir) {
                $previewdir = "/" . $previewdir;
            }


            $total = $data['total'];
            $rows = $data['rows'];

            $list = [];

            if ($total > 0) {
                foreach ($rows as $r) {
                    $l = [];
                    $l['id'] = $r['id'];
                    $l['url'] = $previewdir . "/" . $r['file'];
                    $l['name'] = $r['file'];
                    $l['imagetype'] = $r['imagetype'];
                    $l['storage'] = $r['storage'];
                    $l['mimetype'] = $r['mime'];
                    $l['updatetime'] = $r['updatetime'];

                    $l['fullurl'] = $previewdir . "/" . $r['file'];

                    $list[] = $l;
                }
            }

            return json(['total' => $total, 'rows' => $list]);

        }
        return $this->view->fetch();
    }


    public function select()
    {
        if ($this->request->isAjax()) {
            return $this->index();
        }
        return $this->view->fetch();
    }

}
