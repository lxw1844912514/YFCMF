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
use Org\Util\String;
class LoginController extends HomebaseController {
	
	function index(){
	    if(session('hid')){
			if($this->user['user_status']){
				redirect(__ROOT__."/");
			}else{
				$this->display("User:active");
			}
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
		session(null);
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
			if($member['member_list_open']==0){
				$this->error('该用户已被禁用',0,0);
			}
			//更新字段
			$data = array(
				'last_login_time' => time(),
				'last_login_ip' => get_client_ip(0,true),
			);
			$users_model->where(array('member_list_id'=>$member["member_list_id"]))->save($data);
			session('hid',$member['member_list_id']);
			session('user',$member);
			$this->success('登陆成功',U('Login/check_active'),1);
		}
    }
	function forgot_pwd(){
		$this->display("User:forgot_pwd");
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
	function runforgot_pwd(){
		if(IS_POST){
			$member_list_email=I('member_list_email');
			$member_list_username=I('member_list_username');
			$verify =new Verify ();
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
				$find_user=$users_model->where(array("member_list_username"=>$member_list_username))->find();
				if($find_user){
					if(empty($find_user['member_list_email'])){
						//先更新字段邮箱
						$users_model->where(array("member_list_username"=>$member_list_username))->setField('member_list_email',$member_list_email);
						$find_user['member_list_email']=$member_list_email;
					}
					if($find_user['member_list_email']==$member_list_email){
						//发送重置密码邮件
						$activekey=md5($find_user['member_list_id'].time().uniqid());//激活码
						$result=$users_model->where(array("member_list_id"=>$find_user['member_list_id']))->save(array("user_activation_key"=>$activekey));
						if(!$result){
							$this->error('激活码生成失败！',0,0);
						}
						//生成激活链接
						$url = U('Login/pwd_reset',array("hash"=>$activekey), "", true);
						$template = <<<hello
									#username#，你好！<br>
									请点击或复制下面链接进行密码重置：<br>
									<a href="http://#link#">http://#link#</a>
hello;
						$content = str_replace(array('http://#link#','#username#'), array($url,$member_list_username),$template);
						$send_result=sendMail($member_list_email, 'YFCMF密码重置', $content);
						if($send_result['error']){
							$this->error('密码重置发送失败，请尝试登录后，手动发送激活邮件！',0,0);
						}else{
							$this->success('密码重置发送成功,请查收邮件并激活',U('Index/index'),1);
						}
					}else{
						$this->error("邮箱与注册邮箱不一致",0,0);
					}
				}else {
					$this->error("用户不存在！",0,0);
				}
			}
		}
	}
	function pwd_reset(){
	    $users_model=M("member_list");
	    $hash=I("get.hash");
	    $find_user=$users_model->where(array("user_activation_key"=>$hash))->find();
	    if (empty($find_user)){
	        $this->error('重置码无效！',U('Index/index'),0);
	    }else{
			$this->assign("hash",$hash);
	        $this->display("User:pwd_reset");
	    }
	}
	//验证码
	public function verify_reset(){
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
        $verify->entry('pwd_reset');
    }
	function runpwd_reset(){
		if(IS_POST){
			$verify =new Verify ();
			if (!$verify->check(I('verify'), 'pwd_reset')) {
				$this->error('验证码错误',0,0);
			} 
			$users_model=M("member_list");
			$rules = array(
					//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
					array('password', 'require', '密码不能为空！', 1 ),
					array('password','number','密码长度最小5位,最大20位！',1,'5,20'),
					array('repassword', 'require', '重复密码不能为空！', 1 ),
					array('repassword','password','确认密码不正确',0,'confirm'),
					array('hash', 'require', '重复密码激活码不能空！', 1 ),
			);
			if($users_model->validate($rules)->create()===false){
				$this->error($users_model->getError(),0,0);
			}else{
				$password=I('password');
				$hash=I('hash');
				$member_list_salt=String::randString(10);
				$member_list_pwd=encrypt_password($password,$member_list_salt);
				$result=$users_model->where(array("user_activation_key"=>$hash))->save(array('member_list_pwd'=>$member_list_pwd,'user_activation_key'=>'','member_list_salt'=>$member_list_salt));
				if($result){
					$this->success("密码重置成功，请登录！",U("Login/index"),1);
				}else {
					$this->error("密码重置失败，重置码无效！",0,0);
				}
			}
		}
	}
	function check_active(){
		$this->check_login();
		if($this->user['user_status']){
			redirect(__ROOT__."/");
		}else{
			//判断是否激活
			$this->display("User:active");
		}
	}
	//重发激活邮件
	function resend(){
		$this->check_login();
		$current_user=$this->user;
		$users_model=M('member_list');
		if($current_user['user_status']==0){
			if($current_user['member_list_email']){
				$active_options=get_active_options();
				$activekey=md5($current_user['member_list_id'].time().uniqid());//激活码
				$result=$users_model->where(array("member_list_id"=>$current_user['member_list_id']))->save(array("user_activation_key"=>$activekey));
				if(!$result){
					$this->error('激活码生成失败！',0,0);
				}
				//生成激活链接
				$url = U('Register/active',array("hash"=>$activekey), "", true);
				$template = $active_options['email_tpl'];
				$content = str_replace(array('http://#link#','#username#'), array($url,$current_user['member_list_username']),$template);
				$send_result=sendMail($current_user['member_list_email'], $active_options['email_title'], $content);
				if($send_result['error']){
					$this->error('激活邮件发送失败，请尝试登录后，手动发送激活邮件！',0,0);
				}else{
					$this->success('激活邮件发送成功,请查收邮件并激活',U('Login/index'),1);
				}
			}else{
				$this->error('您的账号未设置邮箱，无法激活！',U('Login/index'),0);
			}
		}else{
		    $this->error('您的账号已经激活，无需再次激活！',U('Index/index'),0);
		}
	}
}