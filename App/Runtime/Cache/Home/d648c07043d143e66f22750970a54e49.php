<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <?php include("App/Home/View/Default/common/head.html"); ?>
</head>
<body>
    <!-- 页头 -->
    <?php include("App/Home/View/Default/common/header.html"); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1">
                <h1>查询结果</h1>
                <h3>快递单号：<?php echo ($track_pin); ?></h3>
                <h4>History</h4>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>日期</th>
                        <th>时间</th>
                        <th>时区</th>
                        <th>地点</th>
                        <th>详细</th>
                        <th>存放地点</th>
                        <th>签名人</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($track_result)): foreach($track_result as $key=>$val): ?><tr>
                            <td><?php if (!empty($val['event-date'])) echo $val['event-date']; ?></td>
                            <td><?php if (!empty($val['event-time'])) echo $val['event-time']; ?></td>
                            <td><?php if (!empty($val['event-time-zone'])) echo $val['event-time-zone']; ?></td>
                            <td><?php if (!empty($val['event-site'])) echo $val['event-site']; if (!empty($val['event-province'])) echo ',' . $val['event-province']; ?></td>
                            <td><?php if (!empty($val['event-description'])) echo $val['event-description']; ?></td>
                            <td><?php if (!empty($val['event-retail-name'])) echo $val['event-retail-name']; ?></td>
                            <td><?php if (!empty($val['signatory-name'])) echo $val['signatory-name']; ?></td>
                        </tr><?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <?php include("App/Home/View/Default/common/footer.html"); ?>
</body>
</html>