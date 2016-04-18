<?php
namespace Common\Controller;
use Think\Controller;
class CommonController extends Controller{
    //空操作
    public function _empty(){
        $this->error('此操作无效');
    }
}