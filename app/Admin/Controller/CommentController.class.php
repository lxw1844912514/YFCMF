<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AuthController;
class CommentController extends AuthController {

	/*
     * 评论列表
	 * @author rainfer <81818832@qq.com>
     */
	public function comment_list(){
		$count=M('comments')->count();
		$Page= new \Think\Page($count,C('DB_PAGENUM'));
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
		$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$join = "".C('DB_PREFIX').'member_list as b on a.uid =b.member_list_id';
		$comments=M('comments')->alias("a")->join($join)->limit($Page->firstRow.','.$Page->listRows)->order('createtime desc')->select();
		$this->assign('comments',$comments);
		$this->display();
	}
	/*
     * 评论删除
	 * @author rainfer <81818832@qq.com>
     */
	public function comment_del(){
		$p=I('p');
		$c_id=I('c_id');
		$rst=M('comments')->where(array('c_id'=>$c_id))->find();
		if($rst){
			$path=$rst['path'];
			//所有以$path开头的都删除
			$rst=M('comments')->where(array('path'=>array('like',$path.'%')))->delete();
			if($rst!==false){
				$this->success('留言删除成功',U('comment_list',array('p'=>$p)),1);
			}else{
				$this->error('评论删除失败',0,0);
			}
		}else{
			$this->error('评论不存在',0,0);
		}
	}
	public function comment_alldel(){
		$p = I('p');
		$ids = I('c_id');
		if(empty($ids)){
			$this -> error("请选择删除的评论",0,0);
		}
		if(!is_array($ids)){
			$ids[]=$ids;
		}
		$ids_arr=array();
		foreach($ids as $c_id){
			$rst=M('comments')->where(array('c_id'=>$c_id))->find();
			if($rst){
				$path=$rst['path'];
				$arr=M('comments')->where(array('path'=>array('like',$path.'%')))->delete();
			}
		}
		$this->success("评论删除成功",U('comment_list',array('p'=>$p)),1);
	}
	public function comment_state(){
		$id=I('x');
		$status=M('comments')->where(array('c_id'=>$id))->getField('c_status');//判断当前状态情况
		if($status==1){
			$statedata = array('c_status'=>0);
			$auth_group=M('comments')->where(array('c_id'=>$id))->setField($statedata);
			$this->success('未审',1,1);
		}else{
			$statedata = array('c_status'=>1);
			$auth_group=M('comments')->where(array('c_id'=>$id))->setField($statedata);
			$this->success('已审',1,1);
		}
	}
	/*
     * 评论设置
	 * @author rainfer <81818832@qq.com>
     */
	public function comment_setting(){
		$csys=C('COMMENT');
		$this->assign('csys',$csys);
		$this->display();
	}
	public function runcsys(){
		$t_open=I('t_open');
		$t_limit=I('t_limit',60,'intval');
		$data = array(
			'COMMENT' => array(
				'T_OPEN'=> $t_open,
				'T_LIMIT'=> $t_limit,
			),
		);
		$rst=sys_config_setbyarr($data);
		if($rst){
			$this->success('评论设置成功',U('comment_setting'),1);
		}else{
			$this->error('评论设置失败',0,0);
		}
	}
}