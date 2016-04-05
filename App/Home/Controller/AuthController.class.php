<?php
/**
 * 身份认证模块
 */

namespace Home\Controller;
use Think\Controller;

class AuthController extends Controller {
    private static $m_usr = "ameadmin";
    private static $m_pwd = "Wingon123@";

    public function admin_login($usr=null, $pwd=null) {
        if ($usr === self::$m_usr && $pwd === self::$m_pwd) {
            session("auth", "ame_admin");
            return true;
        }
        return false;
    }

    public function is_admin() {
        return session("auth") == "ame_admin";
    }
}
