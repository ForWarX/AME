<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
    <form action="" method="post">
        <label for="pin">PIN</label>
        <input type="text" name="pin" id="pin">
        <label for="real">正式</label>
        <input type="radio" name="type" id="real" value="real">
        <label for="test">测试</label>
        <input type="radio" name="type" id="test" value="test">
        <select name="api">
            <option value="summary">Summary</option>
            <option value="detail">Detail</option>
        </select>
        <input type="submit" value="submit">
    </form>
</body>
</html>