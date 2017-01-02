<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\controller\Common;
use think\captcha\Captcha;
use think\Db;
class Login extends Common {
	//登入页面
	public function login(){
		//已登录,跳转到首页
		if(session('aid')){
			$this->redirect('Index/index');
		}
		return $this->fetch();
	}
	//验证码
	public function verify()
    {
        if (session('aid')) {
            header('Location: ' . url('Index/index'));
            exit;
        }
		ob_end_clean();
        $verify = new Captcha (config('verify'));
        return $verify->entry('aid');
    }
	//登陆验证
	public function runlogin(){
		if (!request()->isAjax()){
			$this->error("提交方式错误！",url('Login/login'));
		}else{
			$admin_username=input('admin_username');
			$password=input('admin_pwd');
			if(config('geetest.geetest_on')){
                if(!geetest_check(input('post.'))){
                    $this->error('验证不通过',url('Login/login'));
                };
            }else{
                $verify =new Captcha ();
                if (!$verify->check(input('verify'), 'aid')) {
                    $this->error('验证码错误',url('Login/login'));
                }
            }
			$admin=Db::name('admin')->where(array('admin_username'=>$admin_username))->find();
			if (!$admin||encrypt_password($password,$admin['admin_pwd_salt'])!==$admin['admin_pwd']){
				$this->error('用户名或者密码错误，重新输入',url('Login/login'));
			}else{
				//检查是否弱密码
				session('admin_weak_pwd', false);
				$weak_pwd_reg = array(
					'/^[0-9]{0,6}$/',
					'/^[a-z]{0,6}$/',
					'/^[A-Z]{0,6}$/'
				);
				foreach ($weak_pwd_reg as $reg) {
					if (preg_match($reg, $password)) {
						session('admin_weak_pwd', true);
						break;
					}
				}
				//登录后更新数据库，登录IP，登录次数,登录时间
				$data=array(
                    'admin_last_ip'=>$admin['admin_ip'],
                    'admin_last_time'=>$admin['admin_time'],
					'admin_ip'=>request()->ip(),
                    'admin_time'=>time(),
				);
                //dump($data);
				Db::name('admin')->where(array('admin_username'=>$admin_username))->setInc('admin_hits',1);
				Db::name('admin')->where(array('admin_username'=>$admin_username))->update($data);

				//根据需要决定是否记录前台登陆
				session('hid',$admin['member_id']);
				cookie('yf_logged_user', jiami("{$admin['member_id']}.{$data['admin_last_time']}"));
				$member=Db::name('member_list')->where('member_list_id',$admin['member_id'])->find();
				session('user',$member);
				
				session('aid',$admin['admin_id']);
				//记录对应会员ID
				session('member_id',$admin['member_id']);				
				session('admin_username',$admin['admin_username']);
				session('admin_realname',$admin['admin_realname']);
				session('admin_avatar',$admin['admin_avatar']);
				session('admin_last_change_pwd_time', $admin ['admin_changepwd']);
				$this->success('恭喜您，登陆成功',url('admin/Index/index'));
			}
		}
	}
	/*
	 * 退出登录
	 */
	public function logout(){
		session('aid',null);
		session('member_id',null);
		session('admin_username',null);
		session('admin_realname',null);
		session('admin_avatar',null);
		session('admin_last_change_pwd_time', null);
		$this->redirect('Login/login');
	}
}