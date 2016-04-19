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
            $orders = $model->where($where)->order('ame_no desc')->limit($Page->firstRow.','.$Page->listRows)->select();

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

    // 推送订单给威盛
    public function push_to_ws() {
        if ($this->auth_check()) {
            if (IS_POST || true) {
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
                $model = M("order");
                $order_data = $model->where("id=%d", $id)->find();
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
                // 设置公司地址
                if (empty($order_data['s_company'])) {
                    $order_data['s_company'] = $order_data['s_name'];
                }
                // 检查是否有商品数据以及是否备案
                $model = M("order_goods");
                $id_list = $model->where("order_id=%d", $id)->select();
                if (empty($id_list)) {
                    $result = array("state"=>"error", "msg"=>"訂單無商品");
                    if (IS_AJAX) {
                        $this->ajaxReturn($result);
                    } else {
                        dump($result);
                        exit;
                    }
                }
                $goods_id = array();
                foreach($id_list as $val) {
                    $goods_id[] = $val['good_id'];
                }
                $map = array();
                $map['id'] = array("IN", $goods_id);
                $model = M("goods_record");
                $goods_data = $model->where($map)->select();
                foreach($goods_data as $val) {
                    if ($val['state'] != "done") {
                        $result = array("state"=>"error", "msg"=>"商品（條形碼 " . $val['code'] . "）未備案");
                        if (IS_AJAX) {
                            $this->ajaxReturn($result);
                        } else {
                            dump($result);
                            exit;
                        }
                    }
                }

                // 威盛配置
                $url = self::$config_ws['url'];
                $appname =  self::$config_ws['appname'];
                $appid =  self::$config_ws['appid'];
                $ware_house =  self::$config_ws['ware_house'];
                $exp_no =  self::$config_ws['exp_no'];
                $key =  self::$config_ws['key'];

                $data='{"appname":"' . $appname .
                    '","appid":"' . $appid .
                    '","orders":[{"OpType":"N","OrderNo":"7459174169239","TrackingNo":"123123","WarehouseCode":"' . $ware_house . '","Weight":"12","ExpressName":"' . $exp_no . '","Remark":"","PayType":"ALIPAY","PayMoney":"200","PaySerialNo":"12124545454545","PacksCount":"3","Shipper":{"SenderName":"YHD","SenderCompanyName":"YHD","SenderCountry":"US","SenderProvince":"Beaverton","SenderCity":"Beaverton","SenderAddr":"Wherexpress 7858 SW Nimbus Ave. Beaverton, OR 9700","SenderZip":"","SenderTel":"+1 510-508-631212"},"Cosignee":{"RecPerson":"兰ww","RecPhone":"187217222244","RecMail":"187217222244","RecCountry":"CN","RecProvince":"上海市","RecCity":"上海市","RecAddress":"上海市浦东新区1155号3A","RecZip":"201204","Name":"兰ww","CitizenID":"330205199702171234"},"Goods":[{"CommodityLinkage":"0508274951","Commodity":"百味来 255g/盒 美国进口","CommodityNum":"1","CommodityUnitPrice":"20.55"},{"CommodityLinkage":"0508273652","Commodity":"星巴克 S咖啡  26.4g/盒 8袋装 美国进口","CommodityNum":"1","CommodityUnitPrice":"48"},{"CommodityLinkage":"0508274633","Commodity":"卡夫 432g/盒 美国进口","CommodityNum":"1","CommodityUnitPrice":"65.5"}]}]}';

                $code = md5($data . $key);
                $data = urlencode(urlencode($data));
                $data = 'EData=' . $data . "&SignMsg=" . $code;

                $result = $this->curl_post($url, $data);
                $data = json_decode($result['data']);
                $data = $result['data'];
                if ($result['info']['http_code'] == 200 && $data->rtnCode == '000000') {
                    // 推送成功
                    $result['state'] = 'success';
                    $result['data'] = $data;
                } else {
                    // 推送失败
                    //$result = array("state"=>"fail", "msg"=>"数据推送失败");
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

    // curl post数据
    private function curl_post($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return array("data"=>$data, "info"=>$info);
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
        "ame_no" => "AME#/單號* 不能為空",
        "s_name" => "Sender/寄件人* 不能為空",
        "s_city" => "City/城市* 不能為空",
        "s_province" => "Prov/省* 不能為空",
        "s_address" => "Address/地址* 不能為空",
        "s_zip" => "Postcode/郵編* 不能為空",
        "s_country" => "Country/國家* 不能為空",
        "s_email" => "Email/電郵* 不能為空",
        "s_phone" => "Phone/電話* 不能為空",
        "r_name" => "Recipient/收件人* 不能為空",
        "r_city" => "City/城市* 不能為空",
        "r_province" => "Prov/省* 不能為空",
        "r_address" => "Address/地址* 不能為空",
        "r_zip" => "Postcode/郵編* 不能為空",
        "r_country" => "Country/國家* 不能為空",
        "r_email" => "Email/電郵* 不能為空",
        "r_phone" => "Phone/電話* 不能為空",
        "r_id" => "China ID #/中國身份證* 不能為空",
    );

    // 威盛配置
    private static $config_ws = array(
        //'url' => 'http://180.153.86.138:8002/index.php?r=order/new', // 主站
        'url' => 'http://218.80.251.194:7000/index.php?r=order/new', // 测试
        'appname' => 'XY-004',
        'appid' => '68D718C824315B57C6F048DA8EB74AA6',
        'ware_house' => 'A056',
        'exp_no' => 'YTO',
        'key' => 'E77112A23EC91AC835BAB08E561B5B23',
    );
}
