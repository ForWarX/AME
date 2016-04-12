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
            // 查询条件
            // id/ame_no
            $where = null;
            $pre_conds = I("pre_conds");
            if (is_string($pre_conds) && !empty($pre_conds)) {
                $pre_conds = explode(",", $pre_conds);
            }
            foreach($pre_conds as $val) {
                if ($where != null) $where .= " AND ";
                $where .= "(ame_no LIKE '%" . $val . "%'";
                $id = intval($val);
                if ($id > 0) $where .= " OR id='" . $id . "'";
                $where .= ")";
            }

            $cond = I("condition");
            if (!empty($cond)) {
                if ($where != null) $where .= " AND ";
                $where .= "(ame_no LIKE '%" . $cond . "%'";
                $id = intval($cond);
                if ($id > 0) $where .= " OR id='" . $id . "'";
                $where .= ")";

                $pre_conds[] = $cond; //记录为之前的查询条件
            }

            if (!empty($pre_conds)) $this->assign("pre_conds", $pre_conds); // 查询条件传入页面

            $model = M('order');
            $count = $model->where($where)->count(); // 记录总数
            $Page = new \Think\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数
            //分页跳转的时候保证查询条件
            if (!empty($pre_conds)) {
                $param = "";
                foreach ($pre_conds as $val) {
                     $param .= $param != "" ? "," . $val : $val;
                }
                if ($param != "") $Page->parameter['pre_conds'] = $param;
            }
            //去除之前的条件后要从分页里删掉
            else if (!empty($Page->parameter['pre_conds'])) {
                unset($Page->parameter['pre_conds']);
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

            $this->display();
        }
    }

    // 订单详情
    public function order_detail($id=0) {
        if ($this->auth_check()) {
            if ($id > 0) {
                // 获取订单信息
                $model = M("order");
                $order = $model->where("id=" . $id)->find();
                // 数据处理
                $order['date'] = date("Y/m/d H:i:s", $order["date"]);           // 日期
                $order['state'] = self::$state_details[$order['state']];        // 订单状态
                // 获取订单关联产品ID
                $model = M("order_goods");
                $goods_list = $model->where("order_id=" . $id)->select();
                if (!empty($goods_list)) {
                    // 获取产品信息
                    $goods_id = array();
                    foreach ($goods_list as $val) {
                        $goods_id[] = $val["good_id"];
                    }
                    $goods_id = implode(",", $goods_id);
                    $model = M("goods_record");
                    $goods = $model->where(array("id" => array("in", $goods_id)))->order("id")->select();
                    // 设置数量（备案信息里默认1）
                    foreach($goods_list as $key=>$val) {
                        $goods[$key]['quantity'] = $val['quantity'];
                    }

                    $this->assign('order', $order);
                    $this->assign('goods', $goods);
                }

                // 获取国家代码
                $model = M("order_country");
                $countries = $model->where("is_open=1")->order("name_en")->select();
                $this->assign("countries", $countries);
            }

            $this->display();
        }
    }

    /*************************************
     * 私有函数
     *************************************/
    // 权限检查
    // $doRedirect: 是否跳转，默认不跳转
    private function auth_check($doRedirect=false) {
        $auth = A("Auth");
        if (!$auth->is_admin()) {
            $auth->auth_reset_time();
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
