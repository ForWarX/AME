$(function() {
    // 检查权限
    var body = $("body");
    if (!body.data("auth")) {
        window.parent.location = body.data("login");
    }
});

// select 选取全部/取消选择
function select_all(e) {
    if (e.is(":checked")) {
        $(".select_tar").attr("checked", "checked");
    } else {
        $(".select_tar").removeAttr("checked");
    }
}

// 模糊查找条件检查
function check_cond(e) {
    var cond = e.find("#condition").val();
    if (cond.indexOf("/") != -1) {
        alert("查询条件不能带有 /");
        return false;
    }
    return true;
}