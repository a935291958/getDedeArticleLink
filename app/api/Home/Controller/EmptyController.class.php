<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2017/12/8
 * Time: 8:43
 */


namespace Admin\Controller;
use Think\Controller;

class EmptyController extends Controller {
    public function index(){
        redirect(__APP__ .'/Home/Index', 0);
    }
}


?>