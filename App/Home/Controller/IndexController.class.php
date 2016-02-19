<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        //获得语言变量信息
        //L()获得全部语言,L(username)获得指定语言
        //$this->assign('lang',L());//把语言值传进页面

        $this->display();
    }
}
