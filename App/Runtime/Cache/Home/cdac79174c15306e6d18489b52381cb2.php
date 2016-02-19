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
    <div class="row" style="margin-top: 10px; margin-bottom: 10px; font-size: 20px;;">
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
        <div class="row" style="height: 450px; margin-bottom: 20px;">
            <div class="col-xs-3 col-xs-offset-1" style="padding-left: 0; height: 100%;">
                <!-- 包裹查询开始 -->
                <form class="track-form" action="<?php echo ROOT_PATH; ?>Home/Track/track" method="post" target="_blank">
                    <h3 class="text-center" style="color: white;;">包裹查询</h3>
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
            <div class="col-xs-7" style="padding-right: 0; height: 100%;">
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
                    <div class="col-xs-4 index-article" style="padding-right: 25px;">
                        <div class="row index-article-header text-center">新闻公告</div>
                        <div class="row index-article-block">
                            <ul class="list-unstyled">
                                <?php for($i=0; $i < 12; $i++) { ?>
                                    <li class="index-article-title">2016年春节期间AME速运服务公告</li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-4 index-article" style="padding-left: 20px; padding-right: 20px;">
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
                    <div class="col-xs-4 index-article" style="padding-left: 25px;">
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
</div>
</body>
</html>