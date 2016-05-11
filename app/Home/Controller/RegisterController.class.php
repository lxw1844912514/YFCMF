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
class RegisterController extends HomebaseController {
	
	function index(){
	    if(session('hid')){ //已经登录时直接跳到首页
	        redirect(__ROOT__."/");
	    }else{
	        $this->display("User:register");
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
        $verify->entry('reg');
    }

	function runregister(){
		if(IS_POST){
			$member_list_username=I('member_list_username');
			$member_list_email=I('member_list_email');
			$password=I('password');
			$repassword=I('repassword');
			$verify=I('verify');
			$verify_obj =new Verify ();
			if (!$verify_obj->check($verify, 'reg')) {
				$this->error('验证码错误',0,0);
			}
			$rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('member_list_email', 'require', '邮箱不能为空！', 1 ),
            array('password','require','密码不能为空！',1),
			array('member_list_username','require','密码不能为空！',1),
			array('password','5,20','密码长度最小5位,最大20位！',1,'length'),
            array('repassword', 'require', '重复密码不能为空！', 1 ),
            array('repassword','password','确认密码不正确',0,'confirm'),
            array('member_list_email','email','邮箱格式不正确！',1), // 验证email字段格式是否正确
			);
			$users_model=M("member_list");
			if($users_model->validate($rules)->create()===false){
				$this->error($users_model->getError(),0,0);
			}
			//用户名需过滤的字符的正则
			$stripChar = '?<*.>\'"';
			if(preg_match('/['.$stripChar.']/is', $member_list_username)==1){
				$this->error('用户名中包含'.$stripChar.'等非法字符！',0,0);
			}
			//判断是否存在
			$where['member_list_username']=$member_list_username;
			$where['member_list_email']=$member_list_email;
			$where['_logic'] = 'OR';
			$result = $users_model->where($where)->count();
			if($result){
				$this->error("用户名或者该邮箱已经存在！",0,0);
			}else{
				$member_list_salt=String::randString(10);
				$active_options=get_active_options();
				$sl_data=array(
					'member_list_username'=>$member_list_username,
					'member_list_salt' => $member_list_salt,
					'member_list_pwd'=>encrypt_password($password,$member_list_salt),
					'member_list_email'=>$member_list_email,
					'member_list_open'=>1,
					'member_list_addtime'=>time(),
					'user_status'=>$active_options['email_active']?0:1,//需要激活,则为未激活状态,否则为激活状态
				);
				$rst=$users_model->add($sl_data);
				if($rst!==false){
					if($active_options['email_active']){
						$activekey=md5($rst.time().uniqid());//激活码
						$result=$users_model->where(array("member_list_id"=>$rst))->save(array("user_activation_key"=>$activekey));
						if(!$result){
							$this->error('激活码生成失败！',0,0);
						}
						//生成激活链接
						$url = U('Register/active',array("hash"=>$activekey), "", true);
						$template = $active_options['email_tpl'];
						$content = str_replace(array('http://#link#','#username#'), array($url,$member_list_username),$template);
						$send_result=sendMail($member_list_email, $active_options['email_title'], $content);
						if($send_result['error']){
							$this->error('激活邮件发送失败，请尝试登录后，手动发送激活邮件！',0,0);
						}else{
							$this->success('激活邮件发送成功,请查收邮件并激活',U('Login/index'),1);
						}
					}else{
						//更新字段
						$data = array(
							'last_login_time' => time(),
							'last_login_ip' => get_client_ip(0,true),
						);
						$sl_data['last_login_time']=$data['last_login_time'];
						$sl_data['last_login_ip']=$data['last_login_ip'];
						$users_model->where(array('member_list_id'=>$rst))->save($data);
						session('hid',$rst);
						session('user',$sl_data);
						$this->success('会员注册成功',U('Index/index'),1);				
					}
				}else{
					$this->error("会员注册失败",0,0);
				}
			}
		}
	}
	//激活
	function active(){
		$hash=I("get.hash","");
		if(empty($hash)){
			$this->error("激活码不存在",0,0);
		}
		$users_model=M("member_list");
		$find_user=$users_model->where(array("user_activation_key"=>$hash))->find();
		if($find_user){
			$result=$users_model->where(array("user_activation_key"=>$hash))->save(array("user_activation_key"=>"","user_status"=>1));
			if($result){
				$find_user['user_status']=1;
				//更新字段
				$data = array(
					'last_login_time' => time(),
					'last_login_ip' => get_client_ip(0,true),
				);
				$find_user['last_login_time']=$data['last_login_time'];
				$find_user['last_login_ip']=$data['last_login_ip'];
				$users_model->where(array('member_list_id'=>$find_user["member_list_id"]))->save($data);
				session('hid',$find_user['member_list_id']);
				session('user',$find_user);
				$this->success('恭喜您，登陆成功',U('Index/index'),1);
			}else{
				$this->error("用户激活失败!",U("Login/index"),0);
			}
		}else{
			$this->error("用户激活失败，激活码无效！",U("Login/index"),0);
		}
	}
}