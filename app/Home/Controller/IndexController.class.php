<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Think\Verify;
use Home\Controller\HomebaseController;
class IndexController extends HomebaseController {
	public function index(){
		$this->display(':index');
	}
	public function verify_msg()
    {
		ob_end_clean();
		$verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 40,
            'imageW' => 150,
            'length' => 4,
            'useCurve' => false,
        ));
        $verify->entry('msg');
    }
	public function addmsg(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$verify =new Verify ();
			if (!$verify->check(I('verify'), 'msg')) {
				$this->error('验证码错误',0,0);
			}
			$data=array(
				'plug_sug_name'=>I('plug_sug_name'),
				'plug_sug_email'=>I('plug_sug_email'),
				'plug_sug_content'=>I('plug_sug_content'),
				'plug_sug_addtime'=>time(),
				'plug_sug_open'=>0,
				'plug_sug_ip'=>get_client_ip(0,true),
			);
			$rst=M('plug_sug')->data($data)->add();
			if($rst!==false){
				$this->success("留言成功",1,1);
			}else{
				$this->error('留言失败',0,0);
			}
		}
	}
}