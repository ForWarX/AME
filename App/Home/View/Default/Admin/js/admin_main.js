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