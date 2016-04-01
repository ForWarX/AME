<?php
/**
 * 订单模块
 */

namespace Home\Controller;
use Think\Controller;

class OrderController extends Controller {
    // 下单界面
    public function order() {
        if (IS_POST) {
            // 提交订单
            nice_print(I("post."));

            $this->assign("post", I("post."));
            //$model = M("order");
        }

        $this->display();
    }
}
