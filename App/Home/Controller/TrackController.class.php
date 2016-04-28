<?php
namespace Home\Controller;
use Think\Controller;

class TrackController extends Controller {
    public function track($ame_no=null) {
        if (empty($ame_no)) {
            $ame_no = I("ame_no");
        }
        if (!empty($ame_no)) {
            $model = M("order");
            $data = $model->field("ame_no,date,state,track_company,track_no")->where("ame_no='" . $ame_no . "'")->find();
            $data['date'] = date("m/d/Y", $data['date']);
            $data['state'] = self::$state_details[$data['state']];
            if ($data['track_company'] == 'WS') { // 追踪威盛
                $result = $this->track_ws($data['track_no']);
                if (!empty($result)) {
                    // 数据处理
                    $result = object_to_array($result);
                    foreach($result['rtnList'] as $key=>$val) {
                        $result['rtnList'][$key]['Remark'] = s2t($val['Remark']); // 简体转繁体
                    }

                    $this->assign('track_result', $result);
                }
            }

            $this->assign("order_info", $data);
        }

        $this->display();
    }

    /**************************************
     * Private Members
     **************************************/
    private static $state_details = array(
        "pending" => "Pending / 待處理",
        "cancel" => "Cancel / 取消",
        "done" => "Done / 完成",
        "empty" => "Empty / 空白",
    );

    /**************************************
     * 辅助函数
     **************************************/
    // 威盛订单追踪
    private function track_ws($track_no=null) {
        if (!empty($track_no)) {
            // 威盛配置
            $url = C('ws_url_track');
            $appname = C('ws_appname');
            $appid = C('ws_appid');
            $key = C('ws_key');

            $data = array(
                'appname' => $appname,
                'appid' => $appid,
                'TrackingID' => $track_no,
            );
            $data = json_encode($data);
            $code = md5($data . $key);
            $data = urlencode(urlencode($data));
            $data = 'EData=' . $data . "&SignMsg=" . $code;

            $result = curl_post($url, $data);
            $data = json_decode($result['data']);

            return $data;
        }

        return null;
    }

    /**************************************
     * Canada Post Functions
     **************************************/

    // Canada Post
    // 参数：pin, api, type(real|test)
    public function cp_track() {
        $pin = I("pin");
        if ($pin != '') {
            $api = I("api") != '' ? I("api") : 'detail';
            if (I("type") == 'real') {
                $username = C("cp_real_username");
                $password = C("cp_real_password");
                $url = C('cp_real_url') . '/vis/track/pin/' . $pin . "/" . $api;
            } else {
                $username = C("cp_test_username");
                $password = C("cp_test_password");
                $url = C('cp_test_url') . '/vis/track/pin/' . $pin . "/" . $api;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            if ($info['http_code'] == 200) {
                $xml = new \SimpleXMLElement($data);
                //nice_print(object_to_array($xml));
                $result = object_to_array($xml);
                $result = $result['significant-events']['occurrence'];
                $this->assign("track_pin", $pin);
                $this->assign("track_result", $result);
            } else {
                //print_r($info);
                $this->assign("track_error", $info);
            }
        }

        $this->display();
    }

    // Canada Post
    public function cp_test($type="test") {
        //echo "type: " . $type . "<br>";
        if ($type == 'real') {
            $username = C("cp_real_username");
            $password = C("cp_real_password");
            $url = C('cp_real_url');
        } else {
            $username = C("cp_test_username");
            $password = C("cp_test_password");
            $url = C('cp_test_url');
        }

        $url .= "/rs/ship/price";
        //$post_data = array("username" => "bob","key" => "12345");
        //$post_data = file_get_contents('1.xml');
        //$username = "6e93d53968881714";
        //$password = "0bfa9fcb9853d1f51ee57a";


        $post_data = '<?xml version="1.0" encoding="utf-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
	<customer-number>0008246386</customer-number>
	<parcel-characteristics>
		<weight>0</weight>
		<dimensions>
            <length>0</length>
            <width>0</width>
            <height>0</height>
        </dimensions>
	</parcel-characteristics>
	<origin-postal-code>K2B8J6</origin-postal-code>
	<destination>
		<domestic>
			<postal-code>J0E1X0</postal-code>
		</domestic>
	</destination>
</mailing-scenario>';


        $header[]="Accept: application/vnd.cpc.ship.rate-v3+xml";
        $header[]="Content-Type: application/vnd.cpc.ship.rate-v3+xml";
        $header[]="Authorization: Basic " . base64_encode("$username:$password");
        $header[]="Accept-language: en-CA";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == 200) {
            //echo "===================<br>";
            //nice_print($info);
            //nice_print($data);
            //echo "<br>===================<br>";
            $xml = new \SimpleXMLElement($data);
            $result = object_to_array($xml);
            /*$data = str_replace("<", "&lt", $data);
            $data = str_replace(">", "&gt", $data);*/
            nice_print($result);
            //nice_print($result);
            //$this->assign("result", $result);
        } else {
            nice_print($info);
            nice_print($data);
            //$this->assign("track_error", $info);
        }
    }
}



