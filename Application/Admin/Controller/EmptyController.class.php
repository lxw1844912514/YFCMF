<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends Controller{
    //空控制器
    public function index(){
        $this->error('此操作无效');
    }
}