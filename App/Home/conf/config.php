<?php
return array(
    //'配置项'=>'配置值'

    // 多语言支持
    'LANG_SWITCH_ON'        => true,   // 默认关闭语言包功能
    'LANG_AUTO_DETECT'      => true,   // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'             => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'          => 'l',		// 默认语言切换变量

    // 汇率
    'EX_RATE_CAD_2_RMB'     => 5, // 加币兑人民币汇率

    // 威盛配置
    'ws_appname' => 'XY-004',
    'ws_appid' => '68D718C824315B57C6F048DA8EB74AA6',
    'ws_ware_house' => 'A056', // 仓库
    'ws_exp_no' => 'YTO', // 圆通
    'ws_key' => 'E77112A23EC91AC835BAB08E561B5B23',
    // 推送
    //'ws_url_push' => 'http://180.153.86.138:8002/index.php?r=order/new', // 主站
    'ws_url_push' => 'http://218.80.251.194:7000/index.php?r=order/new', // 测试
    // 包裹追踪
    //'ws_url_track' => 'http://api.kuajing56.com:8002/index.php?r=order/track', // 主站
    'ws_url_track' => 'http://218.80.251.194:7000/index.php?r=order/track', // 测试
);