<?php
/**
 * var_dump优化版
 * @param $tar
 * @param string $space
 */
function nice_print($tar, $space="") {
    if (is_object($tar) || is_array($tar)) {
        if ($space != "") echo "(<br>";
        foreach($tar as $key=>$val) {
            echo $space . $key . "=>";
            nice_print($val, $space . "----");
            echo "<br>";
        }
        if ($space != "") echo substr($space, 0, -4) . ")";
    } else {
        echo $tar;
    }
}

/**
 * 对象转换成数组
 * @param $obj
 * @return array
 */
function object_to_array($obj) {
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if (is_array($arr)) {
        return array_map(__FUNCTION__, $arr);
    } else {
        return $arr;
    }
}

// 简体转繁体
function s2t($str) {
    return \Org\Util\FanJianConvert::simple2tradition($str);
}

// 繁体转简体
function t2s($str) {
    return \Org\Util\FanJianConvert::tradition2simple($str);
}

// curl post数据
function curl_post($url, $data) {
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