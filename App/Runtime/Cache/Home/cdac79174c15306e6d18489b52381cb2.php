<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <title>AmericoExpress</title>
<meta name="keywords" content="">
<meta name="description" content="">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" type="text/css" href="Public/bootstrap/css/bootstrap.min.css">
<script src="Public/bootstrap/js/jquery.js"></script>
<script src="Public/bootstrap/js/bootstrap.min.js"></script>

<link rel="stylesheet" type="text/css" href="App/Home/View/Default/css/basic.css">
</head>
<body>
    <!-- 页头 -->
    <div class="container-fluid">
    <!-- 页面顶部边框 -->
    <div class="row" style="background-color: #dddddd; height: 20px;;"></div>

    <!-- 页头横幅 -->
    <div class="row">
        <div class="col-xs-3 col-xs-offset-1" style="padding-left: 0;">
            <img class="header-top-pic" src="App/Home/View/Default/img/web/AME_Logo.jpg">
        </div>
        <div class="col-xs-7" style="padding-right: 0;">
            <img class="header-top-pic" style="float: right;" src="http://www.inta.org/TrademarkBasics/FactSheets/PublishingImages/TMBasicsBlank-banner.jpg">
        </div>
    </div>

    <!-- 导航 -->
    <div class="row ame-nav" style="margin-top: 10px; margin-bottom: 10px;">
        <div class="col-xs-10 col-xs-offset-1 ame-bk-color">
            <table class="table table-no-border text-center" style="margin-bottom: 0;">
                <tbody>
                <tr>
                    <td><a href="#">价格查询</a></td>
                    <td><a href="#">企业服务</a></td>
                    <td><a href="#">加盟咨询</a></td>
                    <td><a href="#">特价套餐</a></td>
                    <td><a href="#">AME金融</a></td>
                    <td><a href="#">电商通道</a></td>
                    <td><a href="#">加入我们</a></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <div class="container-fluid">
        <div class="row index-track" style="margin-bottom: 20px;">
            <div class="col-xs-3 col-xs-offset-1" style="padding-left: 0; padding-right: 10px; height: 100%;">
                <!-- 包裹查询开始 -->
                <form class="track-form" action="<?php echo ROOT_PATH; ?>Home/Track/track" method="post" target="_blank">
                    <h3 class="text-center track-title" style="color: white;">包裹查询</h3>
                    <div class="form-group">
                        <label for="pin"></label>
                        <input type="text" name="pin" class="form-control" id="pin" placeholder="输入包裹追踪号">
                    </div>
                    <div class="form-group">
                        <label for="email"></label>
                        <input type="text" class="form-control" id="email" placeholder="邮箱追踪">
                    </div>
                    <button type="submit" class="btn btn-success btn-block" style="color: black; font-weight: bold;">查询</button>
                    <input type="hidden" name="api" value="detail">
                </form>
                <!-- 包裹查询结束 -->
            </div>
            <div class="col-xs-7" style="padding-left: 10px; padding-right: 0; height: 100%;">
                <img style="width: 100%; height: 100%;" src="http://b.fastcompany.net/multisite_files/fastcompany/poster/2013/11/3021587-poster-map.jpg">
            </div>
        </div>

        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 ame-bk-color" style="padding: 15px 0;">
                <div class="row">
                    <div class="col-xs-3 text-center">
                        <a class="index-mid-tab" href="#">
                            <img src="App/Home/View/Default/img/web/AME_Icon_3.png">
                            <span>服务流程</span>
                        </a>
                    </div>
                    <div class="col-xs-3 text-center">
                        <a class="index-mid-tab" href="#">
                            <img src="App/Home/View/Default/img/web/AME_Icon_1.png">
                            <span>联系客服</span>
                        </a>
                    </div>
                    <div class="col-xs-3 text-center">
                        <a class="index-mid-tab" href="#">
                            <img src="App/Home/View/Default/img/web/AME_Icon_2.png">
                            <span>网上下单</span>
                        </a>
                    </div>
                    <div class="col-xs-3 text-center">
                        <a class="index-mid-tab" href="#">
                            <img src="App/Home/View/Default/img/web/AME_Icon_3.png">
                            <span>会员中心</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-10 col-xs-offset-1">
                <div class="row">
                    <div class="col-xs-12 col-md-4 index-article" style="padding-right: 25px;">
                        <div class="row index-article-header text-center">新闻公告</div>
                        <div class="row index-article-block">
                            <ul class="list-unstyled">
                                <?php for($i=0; $i < 12; $i++) { ?>
                                    <li class="index-article-title">2016年春节期间AME速运服务公告</li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-4 index-article" style="padding-left: 20px; padding-right: 20px;">
                        <div class="row index-article-header text-center">活动促销</div>
                        <div class="row index-article-block">
                            <?php for($i=0; $i < 5; $i++) { ?>
                                <div class="index-activity-block">
                                    <img src="App/Home/View/Default/img/web/AME_Icon_Rob.png">
                                    <span class="index-activity-title">americomall网上下单免运费</span>
                                    <p>
                                        活动截止时间：2016-02-25
                                        <br>
                                        剩余时间：2小时56分钟21秒
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-4 index-article" style="padding-left: 25px;">
                        <div class="row index-article-header text-center">常见问题</div>
                        <div class="row index-article-block">
                            <ul class="list-unstyled">
                                <li class="index-article-title">为什么货到转运仓库需要几天才把货物...</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="container-fluid" style="border-top: 2px solid rgb(246,213,159); height: 300px; margin-top: 25px;">
    <div class="row">
        <div class="col-xs-2 col-xs-offset-1 footer-article text-center">
            <h3 class="footer-article-category">服务指南</h3>
            <ul class="list-unstyled">
                <li>服务流程</li>
                <li>会员介绍</li>
                <li>特色团购</li>
                <li>常见问题</li>
                <li>联系客服</li>
            </ul>
        </div>
        <div class="col-xs-2 footer-article text-center">
            <h3 class="footer-article-category">配送方式</h3>
            <ul class="list-unstyled">
                <li>配送查询</li>
                <li>当日送达</li>
                <li>次日送达</li>
                <li>急速1小时</li>
                <li>配送收费标准</li>
            </ul>
        </div>
        <div class="col-xs-2 footer-article text-center">
            <h3 class="footer-article-category">支付方式</h3>
            <ul class="list-unstyled">
                <li>货到付款</li>
                <li>网上支付</li>
                <li>微信支付</li>
                <li>国内支付</li>
                <li>金券使用</li>
            </ul>
        </div>
        <div class="col-xs-2 footer-article text-center">
            <h3 class="footer-article-category">售后指南</h3>
            <ul class="list-unstyled">
                <li>售后政策</li>
                <li>价格保护</li>
                <li>退款说明</li>
                <li>返修/退换货</li>
                <li>取消订单</li>
            </ul>
        </div>
        <div class="col-xs-2 footer-article text-center">
            <h3 class="footer-article-category">关于AME</h3>
            <ul class="list-unstyled">
                <li>关于我们</li>
                <li>联系方式</li>
                <li>金融服务</li>
                <li>商家业务</li>
            </ul>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col-xs-12 text-center">
            <a href="#">网站联盟</a>
            |
            <a href="#">热门搜索</a>
            |
            <a href="#">友情链接</a>
            |
            <a href="#">AME社区</a>
            |
            <a href="#">诚征英才</a>
            |
            <a href="#">商家登录</a>
            |
            <a href="#">供应商登录</a>
        </div>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col-xs-12 text-center">
            Copyright© AME美洲快递 2007-2016. All Rights Reserved
        </div>
    </div>
</div>
</body>
</html>