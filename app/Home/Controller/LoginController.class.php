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
            'imageH' => 40,
            'imageW' => 150,
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
		if (!$member||encrypt_password($member_list_pwd,$member['member_list_salt'])!==$member['member_list_pwd']){
				$this->error('用户名或者密码错误，重新输入',0,0);
		}else{
			//更新字段
			$data = array(
				'last_login_time' => time(),
				'last_login_ip' => get_client_ip(0,true),
            );
            $users_model->where(array('member_list_id'=>$member["member_list_id"]))->save($data);
			session('hid',$member['member_list_id']);
			session('user',$member);
			$this->success('恭喜您，登陆成功',U('Index/index'),1);
		}
    }
	function forgot_password(){
		$this->display("User:forgot_password");
	}
	//验证码
	public function verify_forgot(){
        if (session('hid')) {
            redirect(__ROOT__."/");
        }
        $verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 40,
            'imageW' => 150,
            'length' => 4,
            'useCurve' => false,
        ));
        $verify->entry('forgot');
    }
	function runforgot_password(){
		if(IS_POST){
			$member_list_email=I('member_list_email');
			$verify=I('verify');
			if (!$verify->check(I('verify'), 'forgot')) {
				$this->error('验证码错误',0,0);
			} 
			$users_model=M("member_list");
			$rules = array(
				//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
				array('member_list_email', 'require', '邮箱不能为空！', 1 ),
				array('member_list_email','email','邮箱格式不正确！',1), // 验证email字段格式是否正确
			);
			if($users_model->validate($rules)->create()===false){
				$this->error($users_model->getError(),0,0);
			}else{
				$find_user=$users_model->where(array("member_list_email"=>$member_list_email))->find();
				if($find_user){
					//发送重置密码邮件
					$this->success("密码重置邮件发送成功！",U('Index/index'),1);
				}else {
					$this->error("邮箱不存在！",0,0);
				}
			}
		}
	}
}