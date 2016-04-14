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
            } else {
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
            // 之前的条件
            $pre_conds = I("pre_conds");
            if (is_string($pre_conds) && !empty($pre_conds)) {
                $pre_conds = explode(",", $pre_conds);
            }
            // 新的条件
            $cond = I("condition");
            if (!empty($cond)) {
                if (!is_array($pre_conds)) $pre_conds = array();
                $pre_conds[] = $cond; //记录为“之前的查询条件”
            }
            foreach($pre_conds as $val) {
                if ($where != null) $where .= " AND ";
                $where .= "(ame_no LIKE '%" . $val . "%'";
                $id = intval($val);
                if ($id > 0) $where .= " OR id='" . $id . "'";
                $where .= ")";
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
            $orders = $model->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            // 数据处理
            foreach($orders as $key=>$val) {
                $orders[$key]['date'] = date("Y/m/d H:i:s", $val["date"]);           // 日期
                $orders[$key]['state_detail'] = self::$state_details[$val['state']]; // 订单状态
            }

            $this->assign('orders',$orders);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
        }

        $this->display();
    }

    // 订单详情：更新
    public function order_detail($id=0) {
        if ($this->auth_check()) {
            if ($id > 0) {
                if (IS_POST) {
                    // 提交更新
                    $data = I("post.");
                    if (!empty($data)) {
                        $model = M("order");
                        $model->where("id=%d", $id)->save($data);
                    }
                    redirect("order_detail/id/" . $id . ".html");
                } else {
                    // 获取订单信息
                    $model = M("order");
                    $order = $model->where("id=" . $id)->find();
                    // 数据处理
                    $order['date'] = date("Y/m/d H:i:s", $order["date"]);           // 日期
                    $order['state_detail'] = self::$state_details[$order['state']]; // 订单状态
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
                        foreach ($goods_list as $key => $val) {
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
            }
        }

        $this->display();
    }

    // 空白订单
    public function empty_order() {
        if ($this->auth_check()) {
            if (IS_POST) {
                // 生成订单
                $order = array();
                $order['date'] = time();
                $order['state'] = 'empty';
                $order['ame_no'] = \Home\Controller\OrderController::create_ame_no(); // 生成订单号

                // 保存订单
                $model_order = M("order");
                $order_id = $model_order->add($order);
                if ($order_id) {
                    session("order_done_id", $order_id);
                    redirect('../Order/order_done.html');
                } else {
                    echo "订单生成失败";
                }
            }
        }

        $this->display();
    }

    // 用户列表
    public function user_list() {
        if ($this->auth_check()) {
            // 查询条件
            $where = null;
            // 之前的条件
            $pre_conds = I("pre_conds");
            if (is_string($pre_conds) && !empty($pre_conds)) {
                $pre_conds = explode(",", $pre_conds);
            }
            // 新的条件
            $cond = I("condition");
            if (!empty($cond)) {
                if (!is_array($pre_conds)) $pre_conds = array();
                $pre_conds[] = $cond; //记录为“之前的查询条件”
            }
            foreach($pre_conds as $val) {
                if ($where != null) $where .= " AND ";
                $where .= "(";
                $where .= "user_id LIKE '%" . $val . "%'";
                $where .= " OR name LIKE '%" . $val . "%'";
                $where .= " OR company LIKE '%" . $val . "%'";
                $where .= " OR country LIKE '%" . $val . "%'";
                $where .= " OR province LIKE '%" . $val . "%'";
                $where .= " OR city LIKE '%" . $val . "%'";
                $where .= " OR address LIKE '%" . $val . "%'";
                $where .= " OR zip LIKE '%" . $val . "%'";
                $where .= " OR phone LIKE '%" . $val . "%'";
                $where .= " OR email LIKE '%" . $val . "%'";
                $where .= ")";
            }

            if (!empty($pre_conds)) $this->assign("pre_conds", $pre_conds); // 查询条件传入页面

            $model= M("user");
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
            $users = $model->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            // 数据处理
            // 获取国家代码
            $model = M("order_country");
            $countries = $model->getField("code,name_cn");
            foreach($users as $key=>$val) { // 处理$user里的国家代码
                $users[$key]['country'] = $countries[$val['country']];
            }

            $this->assign('users',$users);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
        }

        $this->display();
    }

    // 用户详情：添加/更新
    public function user_detail($id=0) {
        if ($this->auth_check()) {
            if ($id > 0) {
                // 更新用户数据
                if (IS_POST) {
                    // 提交更新
                    $data = I("post.");
                    if (!empty($data)) {
                        $model = M("user");
                        $model->where("id=%d", $id)->save($data);
                    }
                    redirect("user_detail/id/" . $id . ".html");
                } else {
                    // 获取用户信息
                    $model = M("user");
                    $user = $model->where("id=" . $id)->find();
                    $this->assign('user', $user);

                    // 获取国家代码
                    $model = M("order_country");
                    $countries = $model->where("is_open=1")->order("name_en")->select();
                    $this->assign("countries", $countries);
                }
            } else {
                // 添加用户数据
                if (IS_POST) {
                    $model = M("user");
                    if ($model->create()) {
                        $id = $model->add();
                        if ($id) {
                            redirect("user_list.html");
                        } else {
                            $this->error("添加用户失败", "user_detail.html", 3);
                        }
                    }
                }

                // 获取国家代码
                $model = M("order_country");
                $countries = $model->where("is_open=1")->order("name_en")->select();
                $this->assign("countries", $countries);
            }
        }

        $this->display();
    }

    // 删除用户
    // 直接调用或者ajax
    public function user_delete($id=0) {
        if ($this->auth_check()) {
            if ($id > 0) {
                $model = M("user");
                $result = $model->where("id=%d", $id)->delete();

                if ($result) {
                    if (IS_AJAX) {
                        $this->ajaxReturn(array("result"=>true));
                    } else {
                        redirect("../../user_list.html");
                    }
                } else {
                    $this->error("删除用户失败", "user_list", 3);
                }
            } else {
                $this->error("未指定用户", "user_list", 3);
            }
        }

        $this->display("empty");
    }

    // 商品列表
    public function good_list() {
        if ($this->auth_check()) {
            // 查询条件
            // id/ame_no
            $where = null;
            // 之前的条件
            $pre_conds = I("pre_conds");
            if (is_string($pre_conds) && !empty($pre_conds)) {
                $pre_conds = explode(",", $pre_conds);
            }
            // 新的条件
            $cond = I("condition");
            if (!empty($cond)) {
                if (!is_array($pre_conds)) $pre_conds = array();
                $pre_conds[] = $cond; //记录为“之前的查询条件”
            }
            foreach($pre_conds as $val) {
                if ($where != null) $where .= " AND ";
                $where .= "(";
                $where .= "code LIKE '%" . $val . "%'";
                $where .= " OR brand LIKE '%" . $val . "%'";
                $where .= " OR name_cn LIKE '%" . $val . "%'";
                $where .= " OR name_en LIKE '%" . $val . "%'";
                $where .= ")";
            }

            if (!empty($pre_conds)) $this->assign("pre_conds", $pre_conds); // 查询条件传入页面

            $model = M("goods_record");
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
            $goods = $model->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            // 数据处理
            foreach($goods as $key=>$val) {
                $goods[$key]['state_detail'] = self::$record_state[$val['state']]; // 订单状态
            }

            $this->assign('goods',$goods);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
        }

        $this->display();
    }

    // 商品详情：更新
    public function good_detail($id=0) {
        if ($this->auth_check()) {
            if ($id > 0) {
                // 更新商品数据
                if (IS_POST) {
                    // 提交更新
                    $data = I("post.");
                    if (!empty($data)) {
                        $model = M("goods_record");
                        $model->where("id=%d", $id)->save($data);
                    }
                    redirect("good_detail/id/" . $id . ".html");
                } else {
                    // 获取商品信息
                    $model = M("goods_record");
                    $good = $model->where("id=" . $id)->find();
                    $good['state_detail'] = self::$record_state[$good['state']];
                    $this->assign('good', $good);
                }
            }
        }

        $this->display();
    }

    // ajax 商品备案状态更改
    public function ajax_good_state($id=null, $state=null) {
        if ($this->auth_check()) {
            if (IS_AJAX && !empty($id) && !empty($state)) {
                $model = M("goods_record");
                $data = array("state" => $state);
                $result = $model->where("id=%d", $id)->save($data);
                $data['result'] = $result !== false;
                $data['state_detail'] = self::$record_state[$state];
                $this->ajaxReturn($data);
            }
        }

        $this->display("empty");
    }

    /*************************************
     * 私有函数
     *************************************/
    // 权限检查
    // $doRedirect: 是否直接跳转，默认不跳转
    private function auth_check($doRedirect=false) {
        $auth = A("Auth");
        if (!$auth->is_admin()) {
            if ($doRedirect) {
                redirect('login.html');
            }
            else {
                $this->assign("AUTH", false);
                $this->assign("auth_url", ROOT_PATH . "Home/Admin/login.html");
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
    // 订单状态
    private static $state_details = array(
        "pending"    => "Pending / 待处理",
        "cancel"     => "Cancel / 取消",
        "done"       => "Done / 完成",
        "empty"      => "Empty / 空白",
    );

    // 备案状态
    private static $record_state = array(
        "pending"    => "Pending / 待处理",
        "cancel"     => "Cancel / 取消",
        "done"       => "Done / 完成",
        "submitted"  => "Submitted / 已提交",
    );
}
