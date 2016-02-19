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
                nice_print(object_to_array($xml));
            } else {
                print_r($info);
            }
        }

        $this->display();
    }
}