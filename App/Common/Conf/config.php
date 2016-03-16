<?php
return array(
    //'配置项'=>'配置值'

    // 禁止访问的模块
    'MODULE_DENY_LIST'       => array('Common', 'Runtime', 'Upload'),

    // 数据库
    'DB_TYPE'                => 'mysql',
    'DB_HOST'                => 'localhost',
    'DB_NAME'                => 'ameri670_ame',
    'DB_USER'                => 'root',
    'DB_PWD'                 => '',
    'DB_PORT'                => '3306',
    'DB_PREFIX'              => 'ame_',

    // 模板主题
    'DEFAULT_THEME'          => 'Default',
    'TMPL_LOAD_DEFAULTTHEME' => true,

    // session
    'SESSION_OPTIONS'        => array(
        'expire' =>3600
    ),

    // Canada Post 测试账号
    'cp_test_username' => '04f798c5462bfdf3',
    'cp_test_password' => '829a32934e50581631527a',
    'cp_test_url' => 'https://ct.soa-gw.canadapost.ca',
    // Canada Post 实际账号
    'cp_real_username' => '5ddd2a5445a379c6',
    'cp_real_password' => 'ab675c3175bc4877d4c3e9',
    'cp_real_url' => 'https://soa-gw.canadapost.ca',
);