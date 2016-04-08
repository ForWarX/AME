$(function() {
    // 检查权限
    if (!$("body").data("auth")) {
        window.parent.location = "login.html";
    }
});