<?php
return array(
    //'配置项'=>'配置值'
    //数据库
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'mgzch120', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => '935291958', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'gk_', // 数据库表前缀
    'DB_CHARSET' => 'utf8', // 字符集
    'URL_MODEL' => 1,

    /*自定义属性*/
    'DOWN_FILE' => 'files/arcLink.csv',//导出文章链接保存的文件
    'NOT_SHOW_DB'=>array('information_schema', 'performance_schema', 'mysql'), //禁止访问的数据库
    'VALIDATION_SUCCESS_FILE'=>'files/res/success.csv',//验证链接成功保存的结果文件
    'VALIDATION_FAIL_FILE'=>'files/res/fail.csv',//验证链接失败保存的结果文件

);