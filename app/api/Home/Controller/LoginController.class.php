<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2017/11/15
 * Time: 9:59
 */

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    private $user = 'root';
    private $pass = '935291958';


    //ajax返回
    protected function aR($msg, $type = 1)
    {
        $aRT['res'] = true;
        $aRF['res'] = false;
        switch ($type) {
            case 1:
                $aRT['msg'] = $msg;
                $this->ajaxReturn($aRT);
                break;
            case 0:
                $aRF['msg'] = $msg;
                $this->ajaxReturn($aRF);
                break;
        }
        die;
    }


    public function index()
    {
        if (IS_POST) {
            $user = I('post.user');
            $pass = I('post.pass');
            if ($user === $this->user && $pass === $this->pass) {
                session('adminData',true);
                $this->aR('登陆成功');
            }else{
                $this->aR('登陆失败',0);
            }


        } else {
            $this->display('index/login');
        }
    }

    public function out()
    {
        session('adminData', null);
        $this->success('您已成功退出', __CONTROLLER__);
    }
}
