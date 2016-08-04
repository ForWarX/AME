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
            // 查询条件：id/ame_no
            $where = "state!='delete'";
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
                $where .= " OR s_name LIKE '%" . $val . "%'";
                $where .= " OR r_name LIKE '%" . $val . "%'";
                $where .= " OR s_phone LIKE '%" . $val . "%'";
                $where .= " OR r_phone LIKE '%" . $val . "%'";
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
            $orders = $model->where($where)->order('ame_no desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            // 数据处理
            foreach($orders as $key=>$val) {
                $orders[$key]['date'] = date("Y/m/d H:i:s", $val["date"]);                            // 日期
                $orders[$key]['state_detail'] = self::$state_details[$val['state']];                  // 订单状态
                $orders[$key]['weight'] = kg2lb($val['weight']);                                      // 重量
                $orders[$key]['track_company'] = self::$track_company_name[$val['track_company']];    // 快递公司
            }

            // 获取商品数及备案数
            $model = M('order_goods');
            $record_model = M('goods_record');
            foreach($orders as $key=>$order) {
                $ids = $model->where("order_id=%d", $order['id'])->getField("good_id", true);
                $orders[$key]['num_total_goods'] = count($ids); // 订单商品总数

                // 备案数
                if (!empty($ids)) {
                    $map['id'] = array('IN', $ids);
                    $map['state'] = 'done';
                    $records_num = $record_model->where($map)->count();
                    $orders[$key]['num_records'] = $records_num;
                }
            }

            $this->assign('orders',$orders); // 赋值订单信息
            $this->assign('page',$show);     // 赋值分页输出

            // 存放区域
            $model = M("store_area");
            $areas = $model->order("area")->getField("area", true);
            $this->assign("areas", $areas);
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
                        if (!empty($data['weight'])) $data['weight'] = lb2kg($data['weight'], 0);
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
                    $order['weight'] = kg2lb($order['weight']);                     // 重量

                    $this->assign('order', $order);

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
    public function order_empty() {
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

    // 空白订单添加内容
    public function order_add($ame_no=null) {
        if ($this->auth_check()) {
            if (IS_POST) {
                // 提交订单
                $data = I("post.");
                $noError = true;

                /* 检验订单完整性开始 */
                // 检验寄/收件人信息
                foreach(self::$err_msg as $key=>$val) {
                    if (empty($data[$key])) {
                        $noError = false;
                        $this->assign("order_error", self::$err_msg[$key]);
                    }
                }
                // 检验产品信息
                $code_list = array();
                if (!empty($data['code'])) {
                    foreach ($data['code'] as $key => $val) {
                        // 检查信息是否不全
                        $noData = empty($val);
                        if (empty($data['brand'][$key]) != $noData
                            || empty($data['name_en'][$key]) != $noData
                            || empty($data['name_cn'][$key]) != $noData
                            || empty($data['spec'][$key]) != $noData
                            || empty($data['quantity'][$key]) != $noData
                            || empty($data['unit_value'][$key]) != $noData
                        ) {
                            $noError = false;
                            $this->assign("order_error", "Goods info missing / 產品信息不全");
                            break;
                        }

                        // 检查信息是否重复
                        if (in_array($val, $code_list)) {
                            $noError = false;
                            $this->assign("order_error", "Duplicate goods info / 產品信息重複");
                            break;
                        } else {
                            $code_list[] = $val;
                        }

                        // 检查价格是否超标
                        if (!is_numeric($data['unit_value'][$key])) {
                            $noError = false;
                            $this->assign("order_error", "Please input correct price / 請輸入正確的金額");
                            break;
                        } else if (floatval($data['unit_value'][$key]) > 100) {
                            $noError = false;
                            $this->assign("order_error", "Price cannot be over C$100 / 單價不能超過100加幣");
                            break;
                        }
                    }
                }
                if ($noError && empty($code_list)) {
                    $noError = false;
                    $this->assign("order_error", "No goods info / 無產品信息");
                }
                /* 检验订单完整性结束 */

                if (!$noError) {
                    $this->assign("post", $data);
                } else {
                    /* 整理数据开始 */
                    // 基本信息
                    $order = array(
                        's_id' => $data['s_id'],
                        's_name' => t2s($data['s_name']),
                        's_company' => t2s($data['s_company']),
                        's_city' => t2s($data['s_city']),
                        's_province' => t2s($data['s_province']),
                        's_address' => t2s($data['s_address']),
                        's_zip' => strtoupper($data['s_zip']),
                        's_country' => $data['s_country'],
                        's_email' => $data['s_email'],
                        's_phone' => $data['s_phone'],
                        'r_name' => t2s($data['r_name']),
                        'r_company' => t2s($data['r_company']),
                        'r_city' => t2s($data['r_city']),
                        'r_province' => t2s($data['r_province']),
                        'r_address' => t2s($data['r_address']),
                        'r_zip' => strtoupper($data['r_zip']),
                        'r_country' => $data['r_country'],
                        'r_email' => $data['r_email'],
                        'r_phone' => $data['r_phone'],
                        'r_id' => $data['r_id'],
                    );

                    // 产品信息
                    $order_goods = array();
                    foreach($data['code'] as $key=>$val) {
                        if (!empty($val)) {
                            $good = array(
                                'code' => $val,
                                'brand' => t2s($data['brand'][$key]),
                                'name_en' => $data['name_en'][$key],
                                'name_cn' => t2s($data['name_cn'][$key]),
                                'spec' => t2s($data['spec'][$key]),
                                'quantity' => $data['quantity'][$key],
                                'unit_value' => $data['unit_value'][$key],
                            );
                            $record = \Home\Controller\OrderController::get_good_record_by_code($val);
                            if (!empty($record)) {
                                $good['id'] = $record['id'];
                            }

                            $order_goods[] = $good;
                        }
                    }

                    // 其它信息
                    $order['date'] = empty($data['date']) ? time() : strtotime($data['date']);
                    $order['state'] = 'pending';
                    $order['ame_no'] = !empty($data['ame_no']) ? $data['ame_no'] : \Home\Controller\OrderController::create_ame_no(); // 订单号
                    /* 整理数据结束 */

                    $model_order = M("order");

                    // 获取订单ID
                    $no = empty($ame_no) ? $data['ame_no'] : $ame_no;
                    if (!empty($no)) $order_id = $model_order->where("ame_no='%s'", $no)->getField("id");

                    // 保存订单
                    if (empty($order_id)) {
                        $order_id = $model_order->add($order);
                    } else {
                        $model_order->where("id=%d", $order_id)->save($order);
                    }
                    if ($order_id) {
                        // 保存产品并与订单关联
                        $model_goods = M("goods_record");
                        $model_order = M("order_goods");
                        foreach($order_goods as $val) {
                            $quantity = $val['quantity'];

                            if (empty($val['id'])) {
                                // 未备案产品进行备案
                                $val['quantity'] = 1; // 备案里数量只需填1
                                $val['state'] = 'pending';
                                $good_id = $model_goods->add($val); // 保存产品
                            } else {
                                $good_id = $val['id'];
                            }

                            // 关联产品与订单
                            $order_good = array(
                                'order_id' => $order_id,
                                'good_id' => $good_id,
                                'quantity' => $quantity
                            );
                            $model_order->add($order_good);
                        }

                        $url = empty($ame_no) ? 'order_list.html' : '../../order_list.html';
                        redirect($url);
                    } else {
                        $this->assign("order_error", "ERROR: Cannot save order / 訂單無法保存");
                    }
                }
            }

            // 赋值订单号
            if (!empty($ame_no)) {
                $this->assign("ame_no", $ame_no);
            }

            // 获取国家代码
            $model = M("order_country");
            $countries = $model->where("is_open=1")->order("name_en")->select();
            $this->assign("countries", $countries);
        }

        $this->display();
    }

    // ajax订单付款状态更改
    public function ajax_order_paid() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $id = I("id");
                $paid = I("paid");
                if (!empty($id)) {
                    $model = M("order");
                    $data['id'] = $id;
                    $data['paid'] = $paid ? 1 : 0;
                    if ($model->save($data)) {
                        $result['state'] = 'success';
                    } else {
                        $result['state'] = 'fail';
                        $result['msg'] = '更新失败';
                    }
                } else {
                    $result['state'] = 'error';
                    $result['msg'] = '缺少ID';
                }

                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax订单状态更改
    public function ajax_order_state($id=null, $state=null) {
        if ($this->auth_check()) {
            if (IS_AJAX && !empty($id) && !empty($state)) {
                $model = M("order");
                $data = array("state" => $state);
                $result = $model->where("id=%d", $id)->save($data);
                $data['result'] = $result !== false;
                $data['state_detail'] = self::$state_details[$state];
                $this->ajaxReturn($data);
            }
        }

        $this->display("empty");
    }

    // ajax订单数据更改
    // post数据的key必须和数据库一致
    // 一次只能更新一个字段
    // 可以设置键名为database的值来指定数据表
    public function ajax_order_data() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $data = I("post.");
                $res['debug'] = $data;
                if (!empty($data['database'])) {
                    $model = M($data['database']);
                    unset($data['database']);
                } else {
                    $model = M("order");
                }

                // 处理要更新的数据
                $key = $data['update_key'];
                $val = $data[$key];
                if ($key == 'weight') $val = lb2kg($val, 0); // 重量单位转换
                $update_data = array($key => $val);
                unset($data['update_key']);
                unset($data[$key]);

                // $data里只剩下条件
                $result = $model->where($data)->save($update_data);

                $res['result'] = $result !== false;
                if ($key == 'weight') $val = kg2lb($val, 0); // 重量单位转换
                $res[$key] = $val;
                $this->ajaxReturn($res);
            }
        }

        $this->display("empty");
    }

    // ajax删除订单里的商品
    public function ajax_delete_order_good() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $order_id = I("order_id");
                $good_id = I("good_id");
                $result['result'] = 'fail';
                if (!empty($order_id) && $order_id > 0 && !empty($good_id) && $good_id > 0) {
                    $model = M("order_goods");
                    $cond = array('order_id'=>$order_id, 'good_id'=>$good_id);
                    $model->where($cond)->delete();
                    $result['result'] = 'success';
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax订单添加商品
    public function ajax_add_order_good() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $order_id = I("order_id");
                $result['result'] = 'fail';
                if (!empty($order_id) && $order_id > 00) {
                    $model = M("order_goods");

                    $good_id = I('good_id');
                    if (empty($good_id)) {
                        // 新产品
                        $model_goods = M('goods_record');
                        $good = array(
                            'code' => I('code'),
                            'brand' => I('brand'),
                            'name_en' => I('name_en'),
                            'name_cn' => I('name_cn'),
                            'spec' => I('spec'),
                            'quantity' => 1,
                            'unit_value' => I('unit_value'),
                            'state' => 'pending',
                        );
                        $good_id = $model_goods->add($good); // 保存产品
                    }
                    // 关联产品与订单
                    $order_good = array(
                        'order_id' => $order_id,
                        'good_id' => $good_id,
                        'quantity' => I('quantity'),
                    );
                    $res = $model->add($order_good);
                    if ($res) {
                        $result['result'] = 'success';
                    }
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
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

    // ajax商品备案状态更改
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

    // 推送订单给威盛
    public function push_to_ws() {
        if ($this->auth_check()) {
            if (IS_POST) {
                // 获取订单ID
                $id = I("order_id");
                if (empty($id)) {
                    $result = array("state"=>"error", "msg"=>"require order_id");
                    if (IS_AJAX) {
                        $this->ajaxReturn($result);
                    } else {
                        dump($result);
                        exit;
                    }
                }

                // 检查数据完整性
                $order_model = M("order");
                $order_data = $order_model->where("id=%d", $id)->find();
                if (!$order_data['paid']) {
                    $result = array("state"=>"error", "msg"=>"訂單未付款");
                    if (IS_AJAX) {
                        $this->ajaxReturn($result);
                    } else {
                        dump($result);
                        exit;
                    }
                }
                $err_check = self::$err_msg;
                foreach($err_check as $key=>$val) {
                    if (empty($order_data[$key])) {
                        $result = array("state"=>"error", "msg"=>$val);
                        if (IS_AJAX) {
                            $this->ajaxReturn($result);
                        } else {
                            dump($result);
                            exit;
                        }
                    }
                }
                // 检查重量
                if ($order_data['weight'] <= 0) {
                    $result = array("state"=>"error", "msg"=>"未填寫重量");
                    if (IS_AJAX) {
                        $this->ajaxReturn($result);
                    } else {
                        dump($result);
                        exit;
                    }
                }
                // 设置公司地址
                if (empty($order_data['s_company'])) {
                    $order_data['s_company'] = $order_data['s_name'];
                }

                /* 检查是否有商品数据以及是否备案 */
                // 获取订单商品信息
                $model = M("order_goods");
                $id_list = $model->where("order_id=%d", $id)->getField('good_id,quantity');
                if (empty($id_list)) {
                    $result = array("state"=>"error", "msg"=>"訂單無商品");
                    if (IS_AJAX) {
                        $this->ajaxReturn($result);
                    } else {
                        dump($result);
                        exit;
                    }
                }
                // 商品ID合集
                $goods_id = array();
                foreach($id_list as $key=>$val) {
                    $goods_id[] = $key;
                }
                $map = array();
                $map['id'] = array("IN", $goods_id);
                // 获取商品数据
                $model = M("goods_record");
                $goods_data = $model->where($map)->getField("id,code,name_cn,unit_value,state");
                // 检查商品数据
                $ex_rate = C("EX_RATE_CAD_2_RMB");
                foreach($goods_data as $key=>$val) {
                    if ($val['state'] != "done") { // 未备案
                        $result = array("state"=>"error", "msg"=>"商品（條形碼 " . $val['code'] . "）未備案");
                        if (IS_AJAX) {
                            $this->ajaxReturn($result);
                        } else {
                            dump($result);
                            exit;
                        }
                    } else {
                        $goods_data[$key]['quantity'] = $id_list[$key]['quantity']; // 设置数量
                        $goods_data[$key]['unit_value'] = (string)($val['unit_value'] * $ex_rate); // 加币转换人民币
                    }
                }

                // 威盛配置
                $url = C('ws_url_push');
                $appname = C('ws_appname');
                $appid = C('ws_appid');
                $ware_house = C('ws_ware_house');
                $exp_no = C('ws_exp_no');
                $key = C('ws_key');

                // 推送数据
                $goods_array = array();
                foreach($goods_data as $val) {
                    $goods_array[] = array(
                        "CommodityLinkage"    => $val['code'],
                        "Commodity"           => $val['name_cn'],
                        "CommodityNum"        => $val['quantity'],
                        "CommodityUnitPrice"  => $val['unit_value'],
                    );
                }
                $data = array(
                    "appname"       => $appname,
                    "appid"         => $appid,
                    "orders"        => array(
                        array(
                            "OpType"        => "N",
                            "OrderNo"       => $order_data['ame_no'],
                            "TrackingNo"    => $order_data['ame_no'],
                            "WarehouseCode" => $ware_house,
                            "Weight"        => $order_data['weight'],
                            "ExpressName"   => $exp_no,
                            "Remark"        => "",
                            "Shipper"       => array(
                                "SenderName"        => $order_data['s_name'],
                                "SenderCompanyName" => $order_data['s_company'],
                                "SenderCountry"     => $order_data['s_country'],
                                "SenderProvince"    => $order_data['s_province'],
                                "SenderCity"        => $order_data['s_city'],
                                "SenderAddr"        => $order_data['s_address'],
                                "SenderZip"         => $order_data['s_zip'],
                                "SenderTel"         => $order_data['s_phone'],
                            ),
                            "Cosignee"      => array(
                                "RecPerson"         => $order_data['r_name'],
                                "RecPhone"          => $order_data['r_phone'],
                                "RecCountry"        => $order_data['r_country'],
                                "RecProvince"       => $order_data['r_province'],
                                "RecCity"           => $order_data['r_city'],
                                "RecAddress"        => $order_data['r_address'],
                                "RecZip"            => $order_data['r_zip'],
                                "Name"              => $order_data['r_name'],
                                "CitizenID"         => $order_data['r_id'],
                            ),
                            "Goods"         => $goods_array,
                        ),
                    ),
                );

                $data = json_encode($data);
                $code = md5($data . $key);
                $data = urlencode(urlencode($data));
                $data = 'EData=' . $data . "&SignMsg=" . $code;

                $result = curl_post($url, $data);
                $data = json_decode($result['data']);
                if ($result['info']['http_code'] == 200) {
                    // 要更新的数据
                    $data_save = array(
                        "track_no"      => $data->orderList[0]->TrackingNo,
                        "track_company" => "WS",
                        "state"         => "done",
                    );

                    // 判断返回值状态
                    if ($data->rtnCode == '000000') {
                        // 推送成功
                        $result['state'] = 'success';
                        $result['data'] = $data;

                        $order_model->where("id=%d", $id)->save($data_save);
                    } else if ($data->rtnCode == '000003') {
                        // 重复推送
                        $result['state'] = 'repeat';
                        $result['msg'] = "重複推送";
                        $result['data'] = $data;

                        $order_model->where("id=%d", $id)->save($data_save);
                    } else {
                        // 推送失败
                        $result = array("state"=>"fail", "msg"=>$data->rtnList[0]->reason);
                    }
                } else {
                    // 推送出错
                    $result['state'] = 'fail';
                    $result['msg'] = '数据推送失败';
                }
                if (IS_AJAX) {
                    $this->ajaxReturn($result);
                } else {
                    dump($result);
                    exit;
                }
            }
        }

        $this->display("empty");
    }

    // 保存海丝路
    public function push_to_other() {
        if ($this->auth_check()) {
            $data['id'] = I('order_id');
            $data['track_no'] = I('track_no');
            $data['track_company'] = strtoupper(I('code'));
            $data['state'] = 'delivery';

            $model = M('order');
            $res = $model->save($data);
            if ($res === false) {
                $result = array("state"=>"fail", "msg"=>"數據更新失敗");
            } else if ($res == 0) {
                $result = array("state"=>"fail", "msg"=>"未找到該訂單");
            } else {
                $data['state_detail'] = self::$state_details[$data['state']];
                $result = array("state"=>"success", "data"=>$data);
            }
            if (IS_AJAX) {
                $this->ajaxReturn($result);
            } else {
                dump($result);
                exit;
            }
        }

        $this->display("empty");
    }

    // 存放区域
    public function store_area() {
        if ($this->auth_check()) {
            $model = M("store_area");

            if (IS_POST) {
                $area = I("area");
                if (empty($area)) {
                    // 删除
                    $model->where("id=%d", I('id', 0))->delete();
                    if (IS_AJAX) {
                        $this->ajaxReturn(array("result"=>"success"));
                    }
                } else {
                    // 添加
                    if ($model->create()) {
                        if (!$model->add()) {
                            $this->assign("error", "添加失败");
                        }
                    }
                }
            }

            $areas = $model->order('area')->select();
            $this->assign("areas", $areas);
        }

        $this->display();
    }

    // ajax修改存放区域
    public function ajax_update_store_area() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $model = M("order");
                $data['id'] = I("id");
                $data['area'] = I("area");
                if ($model->save($data)) {
                    $result['result'] = 'success';
                } else {
                    $result['result'] = 'fail';
                    $result['msg'] = '更新失败';
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax自动生成会员号
    public function ajax_get_member_id() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $id = $this->generate_member_id();
                if ($id !== false) {
                    $result['result'] = 'success';
                    $result['id'] = $id;
                } else {
                    $result['result'] = 'fail';
                    $result['msg'] = '本月会员数已达上限';
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax获取追踪历史记录
    public function ajax_get_order_history() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $id = I("order_id");
                $result['result'] = 'fail';
                if (!empty($id) && $id > 0) {
                    $model = M("order_history");
                    $data = $model->where("order_id='%d'", $id)->order("id desc")->select();
                    $result['result'] = 'success';
                    $result['data'] = $data;
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax添加追踪历史记录
    public function ajax_add_order_history() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $id = I("order_id");
                $result['result'] = 'fail';
                if (!empty($id) && $id > 0) {
                    $history = str_replace("\n", "<br>", I('history'));
                    $model = M("order_history");
                    $data = array(
                        'order_id'=>$id,
                        'history'=>$history,
                    );
                    $id = $model->data($data)->add();
                    if ($id) {
                        $result['result'] = 'success';
                        $result['id'] = $id;
                        $result['history'] = $history;
                    }
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    // ajax删除追踪历史记录
    public function ajax_delete_order_history() {
        if ($this->auth_check()) {
            if (IS_AJAX) {
                $id = I("id");
                $result['result'] = 'fail';
                if (!empty($id) && $id > 0) {
                    $model = M("order_history");
                    $model->delete($id);
                    $result['result'] = 'success';
                }
                $this->ajaxReturn($result);
            }
        }

        $this->display("empty");
    }

    /*************************************
     * 辅助函数
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

    // 自动生成会员号
    // 十位
    // AME#  年份  三位数  月份  两位数
    //  1     16    361     05     92
    private function generate_member_id() {
        $date = explode('-', date("Y-m"));
        $year = substr($date[0], -2);
        $month = $date[1];
        $ame_prefix = '1';
        $id = $ame_prefix . $year; // 新ID

        $model = M('user');
        $data = $model->where('user_id LIKE "' . $id . '%"')->getField('user_id', true);
        usort($data, array($this, "_member_id_sort"));

        if ($month == substr($data[0], 6, 2)) {
            // 当前月份有过会员注册
            $code3 = substr($data[0], 3, 3); // 最新的3位编号
            $code2 = array();
            foreach ($data as $user_id) {
                if (substr($user_id, 6, 2) == $month) $code2[] = intval(substr($user_id, -2));
                else break;
            }
            if (count($code2) < 100) {
                // 还有未注册的2位号
                $code2 = array_diff(range(0, 99), $code2); // 获取未被注册的会员2位编号
                $code2 =  $code2 = sprintf("%02d", array_rand($code2));
            } else {
                // 所有2位号都注册了，增加3位号
                $code3 = intval($code3) + 1;
                if ($code3 < 1000) {
                    $code2 = sprintf("%02d", rand(0, 99));
                } else {
                    // 已达到本月最大注册数
                    return false;
                }
            }
        } else {
            // 当前月份无会员注册
            $code3 = "000";
            $code2 = sprintf("%02d", rand(0, 99));
        }

        $id .= $code3 . $month . $code2;
        return $id;
    }

    // 会员号排序
    private function _member_id_sort($a, $b) {
        $a_month = substr($a, 6, 2);
        $b_month = substr($b, 6, 2);
        $a_code = substr($a, 3, 3);
        $b_code = substr($b, 3, 3);
        if ($a_month > $b_month) return -1;
        elseif ($a_month < $b_month) return 1;
        elseif ($a_code > $b_code) return -1;
        elseif ($a_code < $b_code) return 1;
        return 0;
    }

    /**************************************
     * 成员数据
     **************************************/
    // 订单状态
    private static $state_details = array(
        "pending"    => "Pending / 待处理",
        "cancel"     => "Cancel / 取消",
        "done"       => "Done / 完成",
        "empty"      => "Empty / 空白",
        "delete"     => 'Delete / 删除',
        "delivery"   => 'Delivering / 已發貨',
    );

    // 备案状态
    private static $record_state = array(
        "pending"    => "Pending / 待处理",
        "cancel"     => "Cancel / 取消",
        "done"       => "Done / 完成",
        "submitted"  => "Submitted / 已提交",
    );

    // 错误信息，与页面统一
    private static $err_msg = array(
        "ame_no"      => "AME#/單號* 不能為空",
        "s_name"      => "Sender/寄件人* 不能為空",
        "s_city"      => "City/城市* 不能為空",
        "s_province"  => "Prov/省* 不能為空",
        "s_address"   => "Address/地址* 不能為空",
        "s_zip"       => "Postcode/郵編* 不能為空",
        "s_country"   => "Country/國家* 不能為空",
        "s_email"     => "Email/電郵* 不能為空",
        "s_phone"     => "Phone/電話* 不能為空",
        "r_name"      => "Recipient/收件人* 不能為空",
        "r_city"      => "City/城市* 不能為空",
        "r_province"  => "Prov/省* 不能為空",
        "r_address"   => "Address/地址* 不能為空",
        "r_zip"       => "Postcode/郵編* 不能為空",
        "r_country"   => "Country/國家* 不能為空",
        "r_email"     => "Email/電郵* 不能為空",
        "r_phone"     => "Phone/電話* 不能為空",
        "r_id"        => "China ID #/中國身份證* 不能為空",
    );

    private static $track_company_name = array(
        "WS"      => "威盛",
        "HSL"     => "海丝路",
        "YT"      => "圆通",
        "EMS"     => "EMS",
        "XGYD"    => "香港邮递",
        "OTHER"   => "其它",
    );
}
