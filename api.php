<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);//严格模式，当数据类型不对的时候会报错
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', TRUE); 

//项目名称
define('APP_NAME','api');

// 定义应用目录
define('APP_PATH','./app/api/');

//或者这样设置也可以，任选其一
ini_set('session.cookie_path', '/');

#禁止生成安全文件
define('BUILD_DIR_SECURE', false);

// 引入ThinkPHP入口文件
require './tp3.2/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单