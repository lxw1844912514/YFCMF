<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class EmptyController extends CommonController{
    //空控制器
    public function index(){
        $this->error('此操作无效');
    }
}