<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        //获得语言变量信息
        //L()获得全部语言,L(username)获得指定语言
        //$this->assign('lang',L());//把语言值传进页面

        $news = array(
            "2016年春节期间AME速运服务公告",
            "关于我国近期中东部地区异常天气...",
            "关于2016年国际件运费调整的通知",
            "关于12月19日到12月22日期间...",
            "关于第十四次上海合作组织政府首...",
            "关于\"11.11\"期间电商快件收派...",
            "加币汇率影响下，物流费用的调整...",
            "AME运费人民币折算方法（2016...",
            "AME美洲快递网站系统维护通知...",
            "中国大陆检验检疫：关于7个品牌...",
            "AME\"国际特惠\"升级 跨境出口价"
        );
        $activities = array(
            array("title"=>"americomall网上下单免运费", "end_time"=>"1456254969"),
            array("title"=>"PBN网上下单满100运费5折", "end_time"=>"1456341363"),
            array("title"=>"春节特价无首磅", "end_time"=>"1456254968"),
            array("title"=>"保健品2.99/磅超低价", "end_time"=>"1456427768"),
            array("title"=>"下载AME手机端APP，赢大奖", "end_time"=>"1456427755")
        );
        $cur_time = time();
        $this->assign("news", $news);
        $this->assign("activities", $activities);
        $this->assign("cur_time", $cur_time);

        $this->display();
    }
    public function price_check(){
        $this->display();
    }
    public function about_us(){
        $this->display();
    }
    public function login(){
        $this->display();
    }
    public function Jingdong_channel(){
        $this->display();
    }
    public function customer_process(){  //服务流程
        $this->display();
    }
    public function notes(){ //理赔免责说明
        $this->display();
    }
    public function send_policy(){ //发货原则
        $this->display();
    }
    public function customs(){ //海关政策
        $this->display();
    }
    public function restriction_goods(){ //禁限运品
        $this->display();
    }
    public function info_express(){
        $this->display();
    }
    public function membership(){
        $this->display();
    }
}
