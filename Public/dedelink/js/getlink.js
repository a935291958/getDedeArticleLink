//页面载入初始化
try {
    localStorage.setItem('getTab', '');
} catch (err) {
    layer.alert('页面初始化错误！');

}


//实例化表单对象，用户选择数据库
let f = layui.form;
f.on('select(database)', function (data) {
    let v = data.value;
    if (!v) {
        layer.alert('请选择数据库');
        $('#showTables').css({'display': 'none'});
        return false;
    }
    $.ajax({
        url: CONTROLLER + '/api',
        data: {'type': 'getTab', 'database': v},
        success(queRes) {
            if (!queRes.res) {
                layer.alert('暂无数据');
                $('#showTables').css({'display': 'none'});
                return false;
            }
            $data = queRes.data;
            //console.table(queRes.data);
            //存储到本地
            localStorage.setItem('getTab', JSON.stringify($data));

            //显示'查看数据表'
            $('#prefix').val($data['prefix']);
            $('#showTables').css({'display': 'block'});
        },
        error: function () {
            layer.alert('请求失败');

        }
    })
});


//点击查看数据表
$('#showTables').click(function () {
    $html = '<input class="layui-input"  value="a" type="text">';
    $boxIndex = layer.open({
        type: 2,
        content: [CONTROLLER + '/showTab', 'yes'],
        maxmin: true,
        title: '数据表',
        scrollbar: true,
        area: ['auto', '400px']
    });

});


//表单提交
layui.form.on('submit(formSub)', function (data) {

    $field = data.field;//当前容器的全部表单字段，名值对形式：{name: value}

    for ($i = 0; $i < $field.length; $i++) {
        if (!$field[$i]) {
            layui.alert("请填写完整");
            return false;
        }
    }
    $loadIndex = layer.load();

    $field['type'] = 'getAceLink';

    $.ajax({
        url: CONTROLLER + '/api',
        data: $field,
        success(queRes) {
            if('object' !== typeof queRes){
                layer.alert('返回数据有误');
                return false;
            }
            if (!queRes['res']) {
                layer.alert(queRes['msg']);
                return false;
            }
            window.open(location['origin']+'/'+queRes['link']);

        },
        error() {
            layer.alert('请求失败');
        },
        complete() {
            layer.close($loadIndex);
        }
    });


    return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
});

