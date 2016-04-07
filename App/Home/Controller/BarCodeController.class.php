<?php
/**
 * 条形码模块
 */

namespace Home\Controller;
use Think\Controller;

class BarCodeController extends Controller {
    public function create($code=null, $text=null) {
        if (empty($code)) {
            $code = I("code");
        }
        if (empty($text)) {
            $text = I("text");
        }

        $barcode = new \Org\Barcode\Barcode128($code, $text);
        $barcode->createBarCode();
    }
}