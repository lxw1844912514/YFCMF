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
class CenterController extends HomebaseController {
	protected function _initialize(){
		parent::_initialize();
		$this->check_login();
	}
	public function index() {
		$this->assign($this->user);
    	$this->display("User:center");
    }
    //编辑用户资料
	public function edit() {
		$province = M('Region')->where ( array('pid'=>1) )->select ();
		$this->assign('province',$province);
		$this->assign($this->user);
    	$this->display("User:edit");
    }
    public function runedit() {
    	if(IS_POST){
    		if (M('member_list')->field('member_list_nickname,member_list_sex,member_list_tel,user_url,signature,member_list_province,member_list_city,member_list_town')->create()) {
				if (M('member_list')->where(array('member_list_id'=>$this->userid))->save()!==false) {
					$this->user=M('member_list')->find($this->userid);
					session('user',$this->user);
					$this->success("保存成功！",U("Center/edit"),1);
				} else {
					$this->error("保存失败！",0,0);
				}
			} else {
				$this->error($this->users_model->getError(),0,0);
			}
    	}
    }
	//修改密码
	    public function password() {
		$this->assign($this->user);
    	$this->display("User:password");
    }
	public function runchangepwd() {
    	if (IS_POST) {
			$old_password=I('old_password');
    		$password=I('password');
			$repassword=I('repassword');
    		if(empty($old_password)){
    			$this->error("原始密码不能为空！",0,0);
    		}
    		if(empty($password)){
    			$this->error("新密码不能为空！",0,0);
    		}
			if($password!==$repassword){
    			$this->error("2次密码不一致！",0,0);
    		}
			$member=M('member_list');
    		$user=$member->where(array('member_list_id'=>$this->userid))->find();
			$member_list_salt=$user['member_list_salt'];
    		if(encrypt_password($old_password,$member_list_salt)===$user['member_list_pwd']){
				if(encrypt_password($password,$member_list_salt)==$user['member_list_pwd']){
					$this->error("新密码不能和原始密码相同！",0,0);
				}else{
					$data['member_list_pwd']=encrypt_password($password,$member_list_salt);
					$data['member_list_id']=$this->userid;
					$rst=$member->save($data);
					if ($rst!==false) {
						$this->success("修改成功！",U('Center/index'),1);
					} else {
						$this->error("修改失败！",0,0);
					}
				}
    		}else{
    			$this->error("原始密码不正确！",0,0);
    		}
    	}
    }
	public function avatar(){
        $imgurl=I('post.imgurl');
        //去'/'
        $imgurl=str_replace('/','',$imgurl);
        $data['member_list_headpic']=$imgurl;
        $rst=M('member_list')->where(array('member_list_id'=>$this->userid))->save($data);
        if($rst!==false){
            session('user_avatar',$imgurl);
			$this->user['member_list_headpic']=$imgurl;
            $this->success ('头像更新成功',U('Center/index'),1);
        }else{
            $this->error ('头像更新失败',U('Center/index'),0);
        }
    }
    function bang(){
    	$oauth_user_model=M("OauthUser");
    	$oauths=$oauth_user_model->where(array("uid"=>$this->userid))->select();
    	$new_oauths=array();
    	foreach ($oauths as $oa){
    		$new_oauths[strtolower($oa['oauth_from'])]=$oa;
    	}
    	$this->assign("oauths",$new_oauths);
    	$this->display("User:bang");
    }
	function fav(){
        $join = "".C('DB_PREFIX').'news as b on a.t_id =b.n_id';
		$favorites_model=M("favorites");
        $count= $favorites_model->alias("a")->join($join)->where(array('uid'=>$this->userid))->count();
        $Page= new \Think\Page($count,10);
        $show= $Page->show();// 分页显示输出
        $favorites=$favorites_model->alias("a")->join($join)->where(array('uid'=>$this->userid))->limit($Page->firstRow.','.$Page->listRows)->order('a.id asc')->select();
        $this->assign('page',$show);
		$this->assign("favorites",$favorites);
		$this->display("User:favorite");
	}
    function delete_favorite(){
        $id=I("get.id",0,"intval");
        $p=I("get.p",1,"intval");
        $favorites_model=M("favorites");
        $result=$favorites_model->where(array('id'=>$id,'uid'=>$this->userid))->delete();
        if($result){
            $this->success("取消收藏成功！",U('fav',array('p'=>$p)),1);
        }else {
            $this->error("取消收藏失败！",0,0);
        }
    }
}
