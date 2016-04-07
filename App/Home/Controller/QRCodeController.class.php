<?php
/**
 * 二维码模块
 */

namespace Home\Controller;
use Think\Controller;

class QRCodeController extends Controller {
    public function create($code=null) {
        include_once('phpqrcode/phpqrcode.php');

        if (empty($code)) {
            $code = I('code', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        $code = urldecode($code);

        ob_clean();
        header("Content-Type: image/png");
        \QRcode::png($code, false, 'M', 4, 2);
    }
}