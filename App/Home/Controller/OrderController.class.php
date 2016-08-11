<?php
/**
 * 订单模块
 */

namespace Home\Controller;
use Think\Controller;

class OrderController extends Controller {
    // 下单界面
    public function order($id=0) {
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
            if (!empty($data['code'])) {
                $code_list = array();
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
            } else {
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
                        $record = $this->get_good_record_by_code($val);
                        if (!empty($record)) {
                            $good['id'] = $record['id'];
                        }

                        $order_goods[] = $good;
                    }
                }

                // 其它信息
                $order['date'] = time();
                $order['state'] = 'pending';
                $order['ame_no'] = $this->create_ame_no(); // 生成订单号
                /* 整理数据结束 */

                // 保存订单
                $model_order = M("order");
                $order_id = $model_order->add($order);
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

                    session("order_done_id", $order_id);
                    if ($id > 0) {
                        redirect('../../order_done.html');
                    } else {
                        redirect('order_done.html');
                    }
                } else {
                    $this->assign("order_error", "ERROR: Cannot save order / 訂單無法保存");
                }
            }
        } else if ($id > 0) {
            $model = M("order");
            $data = $model->where("id=%d", $id)->find();
            $this->assign("post", $data);
        }
        
        // 获取国家代码
        $model = M("order_country");
        $countries = $model->where("is_open=1")->order("name_en")->select();
        $this->assign("countries", $countries);

        $this->display();
    }

    // 查看已保存的订单
    // 为了防止查看别人的订单，统一从session里获取订单的id，所以调用此页面前要先存订单id
    // session key: order_done_id
    public function order_done() {
        $id = session("order_done_id");
        if (!empty($id)) {
            // 获取订单信息
            $model = M("order");
            $order = $model->where("id='%d'", $id)->find();

            if (!empty($order)) {
                $order['date'] = date("m/d/Y", $order['date']); // 处理日期
                $this->handle_country_code($order); // 处理国家代码
                // 简体转繁体
                // 暂且不转
                /*
                foreach($order as $key=>$val) {
                    if (is_string($val)) $order[$key] = s2t($val);
                }*/

                $this->assign('order', $order);

                // 获取对应产品id
                $model = M("order_goods");
                $goods_list = $model->where("order_id=" . $id)->order("good_id")->select();

                if (!empty($goods_list)) {
                    // 获取产品信息
                    $goods_id = array();
                    foreach ($goods_list as $val) {
                        $goods_id[] = $val["good_id"];
                    }
                    $goods_id = implode(",", $goods_id);
                    $model = M("goods_record");
                    $goods = $model->where(array("id" => array("in", $goods_id)))->order("id")->select();
                    // 设置数量（备案信息里默认1），产品名称和品牌简体转繁体
                    foreach($goods_list as $key=>$val) {
                        $goods[$key]['quantity'] = $val['quantity'];
                        //暂且不转了
                        //$goods[$key]['name_cn'] = s2t($goods[$key]['name_cn']);
                        //$goods[$key]['brand'] = s2t($goods[$key]['brand']);
                    }

                    $this->assign('goods', $goods);
                }

                // 二维码
                $qr_data = urlencode('http://' . $_SERVER['HTTP_HOST'] . ROOT_PATH . 'Home/Track/track/ame_no/' . $order['ame_no'] . '.html');
                $qr_url = ROOT_PATH . 'Home/QRCode/create.html?code=' . $qr_data;
                $this->assign('QRCode', $qr_url);
            }
        }

        $this->display();
    }

    // 测试储存的订单，跳转到订单完成页
    public function order_test($id=0) {
        session("order_done_id", $id);
        redirect('../../order_done.html');
    }

    // ajax获取用户信息
    // 参数：会员号
    public function ajax_user_data($id=null) {
        if (IS_AJAX && !empty($id)) {
            $model = M("user");
            $user = $model->where("user_id='%s'", $id)->find();
            $this->ajaxReturn($user);
        }
    }

    // ajax获取商品信息
    // 参数：条形码
    public function ajax_good_data($code=null) {
        if (IS_AJAX && !empty($code)) {
            $model = M("goods_record");
            $user = $model->where("code='%s'", $code)->find();
            $this->ajaxReturn($user);
        }
    }

    // ================================================================================================
    //                                             成员数据
    // ================================================================================================

    // 错误信息，与页面统一
    private static $err_msg = array(
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
        "r_phone" => "Phone/電話* 不能為空",
        "r_id" => "China ID #/中國身份證* 不能為空",
    );

    // 国家
    private static $countries = array(
        "US" => "America/美國",
        "CA" => "Canada/加拿大",
        "CN" => "China/中國",
        "TW" => "Taiwan/台灣",
    );

    // ================================================================================================
    //                                             辅助函数
    // ================================================================================================

    // 生成AME订单号
    // 每天刷新，即从001开始
    /*
    public static function create_ame_no() {
        $date = explode('-', date("m-d-Y"));
        $month = intval($date[0]);
        $day = intval($date[1]);
        $year = intval($date[2]);
        $today = mktime(0, 0, 0, $month, $day, $year);

        $model = M("order");
        $last_no = $model->where('date>=' . $today)->order('id desc')->limit(1)->getField('ame_no'); // 获取最后一个单号

        $prefix = "AME" . substr($date[2], 2) . $date[0] . $date[1];
        if (empty($last_no)) {
            $ame_no = $prefix . "0001";
        } else {
            $no = intval(substr($last_no, strlen($prefix))) + 1;
            $ame_no = $prefix . sprintf("%04u", $no);
        }

        return $ame_no;
    }*/

    // 生成AME订单号
    // 不刷新，到9999之后返回0001
    public static function create_ame_no() {
        $date = explode('-', date("m-d-Y"));

        $model = M("order");
        $last_no = $model->order('id desc')->limit(1)->getField('ame_no'); // 获取最后一个单号

        $prefix = "AME" . substr($date[2], 2) . $date[0] . $date[1];
        if (empty($last_no)) {
            $ame_no = $prefix . "0001";
        } else {
            $no = intval(substr($last_no, strlen($prefix)));
            $no = $no < 9999 ? $no+1 : 1;
            $ame_no = $prefix . sprintf("%04u", $no);
        }

        return $ame_no;
    }

    // 根据编码获取产品信息
    public static function get_good_record_by_code($code=null) {
        if ($code != null) {
            $model = M("goods_record");
            $data = $model->where("code='%s'", $code)->find();
            return $data;
        }

        return null;
    }

    // 国家代码转成国家名
    private function handle_country_code(&$order) {
        $order['s_country'] = self::$countries[$order['s_country']];
        $order['r_country'] = self::$countries[$order['r_country']];
    }
}
