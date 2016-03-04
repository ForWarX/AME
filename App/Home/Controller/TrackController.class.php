<?php
namespace Home\Controller;
use Think\Controller;

class TrackController extends Controller {
    public function track(){
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

    public function test($type="test") {
        echo "type: " . $type . "<br>";
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
        $post_data = file_get_contents('1.xml');
        $username = "6e93d53968881714";
        $password = "0bfa9fcb9853d1f51ee57a";

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
            echo "===================<br>";
            nice_print($info);
            nice_print($data);
            echo "<br>===================<br>";
            $xml = new \SimpleXMLElement($data);
            $result = object_to_array($xml);
            $result = $result['significant-events']['occurrence'];
            nice_print($result);
            //$this->assign("result", $result);
        } else {
            nice_print($info);
            nice_print($data);
            //$this->assign("track_error", $info);
        }
    }
}



