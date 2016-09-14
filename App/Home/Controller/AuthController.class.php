<?php
/**
 * 身份认证模块
 */

namespace Home\Controller;
use Think\Controller;

class AuthController extends Controller {
    private static $m_usr = "ameadmin";
    private static $m_pwd = "Pbcc2015@";

    // 管理员登录，成功返回true，失败返回false
    public function admin_login($usr=null, $pwd=null) {
        if ($usr == self::$m_usr && $pwd == self::$m_pwd) {
            session("auth", "ame_admin");
            return true;
        }
        return false;
    }

    // 管理员登出
    public function admin_logout($usr=null, $pwd=null) {
        if (session("?auth")) {
            session("auth", null);
        }
    }

    // 判断是否是管理员
    public function is_admin() {
        return session("auth") == "ame_admin";
    }
}
