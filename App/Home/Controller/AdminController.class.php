<?php
/**
 * 管理员
 */

namespace Home\Controller;
use Think\Controller;

class AdminController extends Controller {
    // AME管理系统主页
    public function index() {
        $auth = A("Auth");
        if (!$auth->is_admin()) {
            redirect('login.html');
        }

        $this->display();
    }

    // AME管理系统登录界面
    public function login() {
        echo "123";
    }
}
