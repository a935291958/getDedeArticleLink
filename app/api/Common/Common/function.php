<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2018/2/17
 * Time: 15:16
 */

/* @fn PHP CURL HTTPS POST OR GET
 * @param  $url string 请求的地址
 * @param  $param  string || array ，有数据的时候才用post方式
 * @param  $headers  array  设置hear头，默认空
 * @return mixed 失败返回false，成功返回string
 */
function request_post(string $url = '', $param = '', array $headers = array())
{


    $postUrl = $url;
    $curlPost = $param;
    $curl = curl_init();//初始化curl
//post提交方式
    if ($param) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    }
    //设置hear头
    if ($headers) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
    curl_setopt($curl, CURLOPT_URL, $postUrl);//抓取指定网页

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($curl);//运行curl
    $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);//获取状态码
    curl_close($curl);

    return array('data'=>$data,'code'=>$httpCode);
}

/* @fn 格式化参数，array转换成URL字符串
 * @param  $post_data array 需要格式化的数组
 * @return string 返回格式化后的字符串s
 */
function fieldUrl(array $post_data): string
{
    $o = "";
    foreach ($post_data as $k => $v) {
        $o .= "$k=" . urlencode($v) . "&";
    }
    return substr($o, 0, -1);

}

/* @fn 获取毫秒级时间戳
 * @return 返回毫秒级时间戳
 */
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}


//ajax返回
function aR($msg, bool $type = true, int $code = 0)
{
    header('Content-type: application/json');

    $data['res'] = $type;
    $data['code'] = $code;

    if (is_string($msg)) {
        $data['msg'] = $msg;
    } elseif (is_array($msg)) {
        $data = array_merge($data, $msg);
        //dump($data);
    }

    //排序
    ksort($data);

    echo json_encode($data);
    die;
}

/*生成文件名*/
function generate()
{
    $length = rand(12, 12);
    $returnStr = '';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for ($i = 0; $i < $length; $i++) {
        $returnStr .= $pattern{rand(0, 61)}; //生成php随机数
    }
    return date('H-i-s---') . $returnStr;
}

/*
 * 下载头像到本地
 * $avatarDir web目录下的哪个目录
 * $avatarUrl 头像的URL地址
 * 成功返回下载文件路径，失败返回false
 */
function getAvatarUrl(string $avatarDir, string $avatarUrl)
{
    if (!is_dir($avatarDir)) {
        return false;
    }
    //需要保存的目录
    $avatarDir .= date('Y') . '/' . date('m') . '/';


    //获取源头像的文件名
    if (preg_match('/\/vi_32\/(.*)/', $avatarUrl, $res)) {
        $res = str_replace(array('\\', '/'), '', $res);
        $oldFilename = $res[1];
    }
    //没有匹配到源文件名的话重新生成一个
    $oldFilename = $oldFilename ?? generate();

    //匹配文件后缀
    preg_match('/^.*?\.(jpg|jpeg|bmp|gif|png)$/', $avatarUrl, $imgRes);
    if (isset($imgRes[1]) && $imgRes[1] !== '') {
        $imgType = $imgRes[1];
    } else {
        //没有匹配到的话加上.jpg后缀
        $imgType = '.jpg';
    }
    $oldFilename .= $imgType;

    //拼接成完整的文件路径
    $fileNameTemp = $avatarDir . $oldFilename;

    //不存在话下载头像到本地
    if (!is_file($fileNameTemp)) {
        //开始下载头像;
        $getFile = new \Org\Net\Http;
        $fileNameTemp = $getFile->curlDownload($avatarUrl, $avatarDir, $oldFilename);

        if ($fileNameTemp) {
            return $fileNameTemp;
        } else {
            return false;
        }
    } else {
        return $fileNameTemp;
    }

}

//阿里API封装
/*
 * $host API地址
 * $appcode 阿里云appcode
 * $success 请求成功且正确返回数据的标志 例如 array('msg'=>'ok')
 * @return 成功返回数据，失败返回false
 */
function aliApi(string $host, string $appcode, array $success = array('msg' => 'ok'))
{
    if (!$host) {
        return false;
    }

    $headers = array("Authorization:APPCODE " . $appcode);
    $res = json_decode(request_post($host, '', $headers), true);


    if ($res[array_keys($success)[0]] === array_values($success)[0]) {
        return $res;
    } else {
        //return $res;
        return false;
    }
}

/* @fn 用户加密函数
 * @param $data string 需要加密的字符串
 * @param $key string 秘钥
 * @param $iv string
 * @return string 返回加密后的字符串
 */
function encrypted(string $data, string $key, string $iv): string
{

    $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
    return base64_encode($encrypted);
}

/* @fn 用户解密函数
 * @param $data string 需要解密的字符串
 * @param $key string 秘钥
 * @param $iv string
 * @return string 返回解密后的字符串
 */
function decrypted(string $data, string $key, string $iv): string
{

    $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
    return base64_encode($encrypted);
}

/* @fn 判断是否有敏感词
 * @param $data string 需要判断的字符串
 * @param $rules string 验证规则，为空的话从数据库取
 * @return bool 没有敏感字返回true,
 */
function sene(string $data, string $rules = ''): bool
{
    if (!$data) {
        return false;
    }
    #获取匹配规则
    if (!$rules) {
        $cfg = D('config');
        $rules = $cfg->getOne('sensitive');
        if (!$rules) {
            return false;
        }
    }
    #验证有没有敏感字
    if (preg_match('/' . $rules . '/i', $data, $res) > 0) {
        return false;
    } else {
        return true;
    }


}


/* @fn 判断是否为手机号码
 * @param $phone  手机号码
 * @return bool 是手机号码的话返回true
 */

function isPhone($phone): bool
{
    if (preg_match('/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/', $phone) > 0) {
        return true;
    } else {
        return false;
    }
}

/* @fn 获取N维数组的值
 * @param $array array 数组
 * @return array 返回下标从0开始的数组
 */
function getArrVal(array $array):array {
    global $temp;
    if(!$array){
        return array();
    }
    foreach ($array as $v){
        if(!is_array($v)){
            $temp[] = $v;
        }else{
            getArrVal($v);
        }
    }
    return $temp;
}