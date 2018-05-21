layui.use('upload', function () {
    let upload = layui.upload;

    //执行实例
    let uploadInst = upload.render({
        elem: '#upFile' //绑定元素
        , url: CONTROLLER + '/getPicname' //上传接口
        , accept: 'file'
        , exts: 'txt|csv'
        ,field:'txt'
        , done: function (queRes) {
            if(!queRes['res']){
                layer.alert('上传失败');
                return false;
            }
            $filePath = queRes['filePath'];
            $('#filePath').val($filePath);
            $('#filePathHref').html($filePath);
            layer.msg('上传成功');


        }
        , error: function () {
            //请求异常回调
        }
    });
});

//单选框，本地的还是线上的
layui.form.on('radio(fileType)', function(data){

    if(data.value === 'server'){
        $('#fMain').css({'display':'none'});
        //$('#formBox')[0].reset();

    }else{
        $('#fMain').css({'display':'block'});
    }
});


//表单提交
layui.form.on('submit(formSub)', function (data) {

    $field = data.field;//当前容器的全部表单字段，名值对形式：{name: value}


    $loadIndex = layer.load();

    $field['type'] = 'valLink';

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
           //$('#downResBox').css({display:'block'});
            window.open('/'+queRes['fail']);
            window.open('/'+queRes['success']);


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
