<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
use Think\Verify;
class LoginController extends CommonController {
	//登入页面
	public function login(){
		//已登录,跳转到首页
		if(session('aid')){
			$this->redirect('Index/index');
		}
		$this->display();
	}

	//登陆验证
	public function runlogin(){
		if (!IS_AJAX){
			$this->error("提交方式错误！",0,0);
		}else{
			$admin_username=I('admin_username');
			$password=I('admin_pwd');
			$verify =new Verify ();
			if (!$verify->check(I('verify'), 'aid')) {
				$this->error('验证码错误',0,0);
			}
			$admin=M('admin')->where(array('admin_username'=>$admin_username))->find();
			if (!$admin||encrypt_password($password,$admin['admin_pwd_salt'])!==$admin['admin_pwd']){
				$this->error('用户名或者密码错误，重新输入',0,0);
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
					'admin_ip'=>get_client_ip(0,true),
                    'admin_time'=>time(),
				);
                //dump($data);
				M('admin')->where(array('admin_username'=>$admin_username))->setInc('admin_hits',1);
				M('admin')->where(array('admin_username'=>$admin_username))->save($data);
				session('aid',$admin['admin_id']);
				session('admin_username',$admin['admin_username']);
				session('admin_realname',$admin['admin_realname']);
				session('admin_avatar',$admin['admin_avatar']);
				session('admin_last_change_pwd_time', $admin ['admin_changepwd']);
				$this->success('恭喜您，登陆成功',1,1);
			}
		}
	}
	public function verify()
    {
        if (session('aid')) {
            header('Location: ' . U('index/index'));
            return;
        }
        $verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 42,
            'imageW' => 250,
            'length' => 5,
            'useCurve' => false,
        ));
        $verify->entry('aid');
    }
	/*
     * 退出登录
     */
	public function logout(){
		session(null);
		$this->redirect('Login/login');
	}
}