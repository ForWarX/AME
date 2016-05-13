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

/**********************************
 * 列表可编辑区域
 **********************************/
// 转换成input
function editable(e) {
    if (!e.data('editing')) {
        e.data('editing', true).attr('id', 'editing');
        var type = e.data('type');
        var url = e.data('url');
        var max_width = Number(e.data('max_width'));
        var name = e.data('name');
        var html = $.trim(e.html());
        var data_num = Number(e.data('data_num'));
        switch (type) {
            case 'text':
                html = '<input type="' + type + '" name="' + name + '" value="' + html + '" onblur="editable_ajax($(this));" data-url="' + url + '"';
                if (max_width > 0) html += ' style="max-width:' + max_width + 'px"';
                if (data_num > 0) {
                    html += ' data-data_num="' + data_num + '"';
                    for (var i = 1; i <= data_num; i++) {
                        html += ' data-key' + i + '="' + e.data("key" + i) + '" data-val' + i + '="' + e.data("val" + i) + '"';
                    }
                }
                html += '>';
                break;
        }
        e.html(html);
    }
}
// post数据
function editable_ajax(e) {
    e.after("处理中...");

    var post_data = {};
    post_data[e.attr('name')] = e.val();
    var data_num = e.data("data_num");
    for(var i=1; i <= data_num; i++) {
        post_data[e.data('key'+i)] = e.data('val'+i);
    }
    $.post(e.data('url'), post_data, function(result) {
        if (result['result']) {
            $('#editing').html(result[e.attr('name')]).data('editing', false).removeAttr('id');
        }
    });
}