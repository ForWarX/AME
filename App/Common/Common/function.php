<?php
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

function object_to_array($obj) {
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if (is_array($arr)) {
        return array_map(__FUNCTION__, $arr);
    } else {
        return $arr;
    }
}