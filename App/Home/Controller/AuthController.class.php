<?php
/**
 * 身份认证模块
 */

namespace Home\Controller;
use Think\Controller;

class AuthController extends Controller {
    static private $m_usr = "ameadmin";
    static private $m_pwd = "Wingon123@";

    public function admin_login($usr=null, $pwd=null) {
        if ($usr === $this->m_usr && $pwd === $this->m_pwd) {
            session("auth", "ame_admin");
            return true;
        }
        return false;
    }

    public function is_admin() {
        return session("auth") == "ame_admin";
    }
}
