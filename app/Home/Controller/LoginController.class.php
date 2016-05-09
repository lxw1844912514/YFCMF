<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Home\Controller\HomebaseController;
use Think\Verify;
class LoginController extends HomebaseController {
	
	function index(){
	    if(session('hid')){
	        redirect(__ROOT__."/");
	    }else{
	        $this->display("User:login");
	    }
	}
	//验证码
	public function verify(){
        if (session('hid')) {
            redirect(__ROOT__."/");
        }
        $verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 42,
            'imageW' => 250,
            'length' => 4,
            'useCurve' => false,
        ));
        $verify->entry('hid');
    }
	/*
     * 退出登录
     */
	public function logout(){
		session('hid',null);
		session('user',null);
		redirect(__ROOT__."/");
	}
	
    //登录验证
    function runlogin(){
		$member_list_username=I('member_list_username');
		$member_list_pwd=I('member_list_pwd');
		$verify =new Verify ();
		if (!$verify->check(I('verify'), 'hid')) {
			$this->error('验证码错误',0,0);
		}    	
    	$users_model=M("member_list");
    	$rules = array(
    			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
    			array('member_list_username', 'require', '手机号/邮箱/用户名不能为空！', 1 ),
    			array('member_list_pwd','require','密码不能为空！',1),
    	
    	);
    	if($users_model->validate($rules)->create()===false){
    		$this->error($users_model->getError(),0,0);
    	}    	
		if(strpos($member_list_username,"@")>0){//邮箱登陆
            $where['member_list_email']=$member_list_username;
        }else{
            $where['member_list_username']=$member_list_username;
        }
		$member=$users_model->where($where)->find();
		if (!$member||md5(md5($member_list_pwd))!==$member['member_list_pwd']){
				$this->error('用户名或者密码错误，重新输入',0,0);
		}else{
			session('hid',$member['member_list_id']);
			session('user',$member);
			$this->success('恭喜您，登陆成功',U('Index/index'),1);
		}
    }
}