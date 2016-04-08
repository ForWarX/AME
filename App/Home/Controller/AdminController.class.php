<?php
/**
 * 管理系统
 */

namespace Home\Controller;
use Think\Controller;

class AdminController extends Controller {
    /*************************************
     * 首页
     *************************************/
    // AME管理系统主页
    public function index() {
        $this->auth_check(true);
        $this->display();
    }

    // 登录界面
    public function login() {
        if (IS_POST) {
            $auth = A("Auth");
            if ($auth->admin_login(I('user'), I('pwd'))) {
                redirect("index.html");
            } else if (false) {
                $this->assign('login_error', '登录失败');
            }
        }

        $this->display();
    }

    // 登出
    public function logout() {
        $auth = A("Auth");
        $auth->admin_logout();
        redirect('login.html');
    }

    /*************************************
     * 内容页面
     *************************************/
    // 欢迎页
    public function welcome() {
        $this->auth_check();
        $this->display();
    }

    // 订单列表
    public function order_list() {
        if ($this->auth_check()) {
            $map = null;
            $model = M('Order');
            $count = $model->where($map)->count(); // 记录总数
            $Page = new \Think\Page($count, 1);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            //分页跳转的时候保证查询条件
            foreach($map as $key=>$val) {
                $Page->parameter[$key] = urlencode($val);
            }
            $show = $Page->show();// 分页显示输出

            // 分页数据查询
            $orders = $model->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            // 数据处理
            foreach($orders as $key=>$val) {
                $orders[$key]['date'] = date("Y/m/d H:i:s", $val["date"]);           // 日期
                $orders[$key]['state'] = self::$state_details[$val['state']];        // 订单状态
            }

            $this->assign('orders',$orders);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
        }

        $this->display();
    }

    /*************************************
     * 私有函数
     *************************************/
    // 权限检查
    // $doRedirect: 是否跳转，默认不跳转
    private function auth_check($doRedirect=false) {
        $auth = A("Auth");
        if (!$auth->is_admin()) {
            if ($doRedirect) {
                redirect('login.html');
            }
            else {
                $this->assign("AUTH", false);
            }
            return false;
        } else {
            $this->assign("AUTH", true);
            return true;
        }
    }

    /**************************************
     * Private Members
     **************************************/
    private static $state_details = array(
        "pending" => "Pending / 待處理",
        "cancel" => "Cancel / 取消",
        "done" => "Done / 完成",
    );
}
