var g_Main_Iframe = $("#admin_main_iframe"); // 内容区域的iframe

$(function() {
    // 侧导航点击切换内容
    $(".menu-second").find("a").on("click", function(e) {
        g_Main_Iframe.attr("src", $(this).data("page"));
        return false;
    });
});
