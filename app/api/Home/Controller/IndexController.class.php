<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{

    protected function _initialize()
    {
        //dump($_SERVER['HTTP_REFERER ']);
        //查询是否登录
        $user = session('adminData');
        //dump($user);
        if (!$user) {
            $this->redirect('/Home/Login');
            die;
        }
        $this->assign(array(
            'adminData' => $user
        ));

    }


    /**用户访问空操作时跳转到index**/
    public function _empty()
    {

        $this->index();

    }

    // 上传单个文件
    protected function portrait()
    {
        if ($_FILES['txt']['name']) { // 如果上传的头像
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 3145728;// 设置附件上传大小
            $upload->exts = array('txt', 'csv');// 设置附件上传类型
            $upload->rootPath = './Uploads_ch/'; // 设置附件上传根目录
            $upload->subName = array('date', 'Y-m-d');//子目录创建规则
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['txt']);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                $portraitPath = 'Uploads_ch/' . $info['savepath'] . $info['savename'];

                return array('filePath' => $portraitPath);
            }
        }
    }

    public function index()
    {
        $notShowDb = C('NOT_SHOW_DB');
        $m = M();
        #获取数据库列表
        $getDbSql = 'show databases';
        $dbList = $m->query($getDbSql);
        $dbListNew = array();
        foreach ($dbList as $k => $v) {
            if (!in_array($v['database'], $notShowDb)) {
                $dbListNew[] = $v['database'];
            }

        }
        #dump($dbListNew);

        $this->assign(
            array(
                'dbListNew' => $dbListNew
            )
        );

        $this->display();
    }

    public function api()
    {
        $type = I('get.type', '', 'string');
        $database = I('get.database', '', 'string');
        $m = M();
        switch ($type) {
            //获取某个数据库的所有表
            case 'getTab':
                $tables = $m->db(1, "mysql://" . C('DB_USER') . ":" . C('DB_PWD') . "@" . C('DB_HOST') . ":" . C('DB_PORT') . "/" . $database . "")->query("show tables");
                if ($tables) {
                    //dump($tables);
                    //dump(getArrVal($tables));
                    $tabVal = getArrVal($tables);
                    $prefixList = [];
                    foreach ($tabVal as $k => $v) {
                        preg_match('/^.*?_/', $v, $res);
                        if ($res[0]) {
                            $prefix = $res[0];
                            if ($prefixList[$prefix]) {
                                $oldSum = $prefixList[$prefix];
                                $prefixList[$prefix] = $oldSum + 1;
                            } else {
                                $prefixList[$prefix] = 1;
                            }
                        }
                    }

                    aR(array('data' => array(
                        'database' => $database,
                        'prefix' => array_search(max($prefixList), $prefixList),
                        'list' => $tabVal,

                    )));
                } else {
                    aR('暂无数据', false, -2);
                }

                break;
            //获取某个数据库的文章链接
            case 'getAceLink':
                $m = $m->db(1, "mysql://" . C('DB_USER') . ":" . C('DB_PWD') . "@" . C('DB_HOST') . ":" . C('DB_PORT') . "/" . $database . "");
                #当前域名
                $host = I('get.host');
                #前缀
                $prefix = I('get.prefix');
                #数据库
                $database = I('get.database');
                if (!$host || !$prefix || !$database) {
                    aR('参数不足', false, -2);
                }

                #查询默认保存的路径
                $cfgSql = 'SELECT `value` FROM ' . $prefix . 'sysconfig WHERE varname="cfg_arcdir" LIMIT 1';
                $cfg_arcdir = ($m->query($cfgSql))[0]['value'];

                if (!$cfg_arcdir) {
                    aR('未查到默认保存的路径', false, -100);
                }

                //dump($cfg_arcdir);
                $sql = 'SELECT ace.title,ace.typeid,arc.typename,ace.arcrank,arc.typedir,arc.namerule,ace.id,ace.pubdate FROM  ' . $prefix . 'archives ace JOIN  ' . $prefix . 'arctype arc ON ace.typeid=arc.id WHERE ace.arcrank=0 AND ace.ismake=1 ORDER BY typeid ASC ';
                $data = $m->query($sql);

                if (!$data) {
                    aR('未查到文章数据', false, -100);
                }


                $resArray = array();
                $resStr = 'ID,标题,栏目,链接' . "\n";
                foreach ($data as $k => $v) {
                    $v['time'] = explode('-', date('Y-m-d', $v['pubdate']));
//                    if(!preg_match('/Y/',$v['namerule'])){
//                        continue;
//                    }
                    $v['link'] = $v['typedir'] . $v['namerule'];
                    $v['link'] = str_replace('{cmspath}', '', $v['link']);
                    $v['link'] = str_replace('{typedir}', '', $v['link']);
                    $v['link'] = str_replace('{aid}', $v['id'], $v['link']);
                    $v['link'] = str_replace('{Y}', $v['time'][0], $v['link']);
                    $v['link'] = str_replace('{M}', $v['time'][1], $v['link']);
                    $v['link'] = str_replace('{D}', $v['time'][2], $v['link']);
                    $v['title'] = str_replace(',', '，', $v['title']);
                    $resArray[$v['typeid']][] = $v;
                    $resStr .= $v['id'] . ',' . $v['title'] . ',' . $v['typename'] . ',' . $host . $v['link'] . "\n";
                }
                file_put_contents(C('DOWN_FILE'), mb_convert_encoding($resStr, 'GBK'));
                aR(array('link' => C('DOWN_FILE')));
                // dump($resStr);
                //dump($resArray);
                break;
            //验证链接的正确性
            case 'valLink':
                set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
                //获取相关配置属性
                $failFile = C('VALIDATION_FAIL_FILE');//失败保存的文件
                $suFile = C('VALIDATION_SUCCESS_FILE');//验证成功保存的结果文件
                $downFile = C('DOWN_FILE');//导出文章链接保存的文件

                //初始化
                $row = '链接,网页title,文章标题' . "\n";
                $failTxt = '链接,状态码'."\n";

                //接受参数
                $data['fileType'] = I('get.fileType');
                $data['separator'] = I('get.separator');
                $data['column'] = I('get.column', false, 'int');
                $data['filePath'] = I('get.filePath');


                if ($data['fileType'] === 'server') {
                    #读取本地文件
                    $fh = fopen($downFile, 'r') ?? aR('读取文件失败', false, -33);
                    $tempLine = [];
                    while (!feof($fh)) {
                        #读取一行
                        $line = fgets($fh);
                        #转换编码
                        $line = iconv('GBK', 'UTF-8', $line);
                        #分割URL
                        $line = str_replace(array("\r\n", "\r", "\n"), '', (explode(',', $line))[3]);
                        #判断是不是URL
                        $isUrl = preg_match("#^[http|https]#", $line);
                        if (!$isUrl) {
                            $failTxt .= $line ."\n";
                            continue;
                        }
                        $tempLine[] = $line;
                        //echo $line;
                        //发出请求获取HTML
                        $reqRes = request_post($line);
                        $html = $reqRes['data'];
                        $code = $reqRes['code'];
                        //echo $html['code'];
                        if ($code != 200) {
                            $failTxt .= $line.',' .$code ."\n";
                            continue;
                        }
                        //匹配编码
                        preg_match_all('/<meta.*charset.*=.*"(.*?)"/', $html, $charsetRes);
                        $charset = $charsetRes[1][0] ?? 'GBK';
                        //var_dump($charset);
                        //转换编码
                        if (strtoupper($charset) !== 'UTF-8') {
                            $html = iconv($charset, 'UTF-8', $html);
                        }
                        //匹配title
                        preg_match('#<title>(.*?)</title>.*?<h3>(.*?)</h3>#ms', $html, $titRes);
                        //var_dump($html);
                        //title
                        $title = trim($titRes[1]);
                        //h3文章标题
                        $h3 = trim($titRes[2]);

                        $row .= $line . ',' . $title . ',' . $h3 . "\n";


                    }
                    fclose($fh);
                    #保存结果
                    file_put_contents($failFile,$failTxt);
                    file_put_contents($suFile,$row);
                    aR(array('fail'=>$failFile,'success'=>$suFile));


                    // dump($tempLine);
                } else {
                    foreach ($data as $v) {
                        if (!$v) {
                            aR('参数不足', false, -1);
                        }
                    }
                }


                break;

            default:
                aR('缺少type参数', false, -1);
        }

    }

//    显示数据表
    public function showTab()
    {
        $this->display();
    }

//    验证链接
    public function validation()
    {
        $this->display();
    }

    //接收单文件，用于封面等异步上传
    public function getPicname()
    {
        $aRF['res'] = false;
        $aRT['res'] = true;
        $info = $this->portrait($_FILES);
        if ($info['filePath']) {
            aR(array('msg' => '上传成功', 'filePath' => $info['filePath']));
        } else {
            aR('上传失败', false, -2);
        }


    }

}