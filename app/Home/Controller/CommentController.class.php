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
class CommentController extends HomebaseController{
	//个人中心-我的评论
	function index(){
		$this->check_login();
		$uid=$this->userid;
		$where=array("uid"=>$uid);
		$count=M('comments')->where($where)->count();
		$Page= new \Think\Page($count,10);
		$show= $Page->show();
        $join = "".C('DB_PREFIX').'news as b on a.t_id =b.n_id';
		$comments=M('comments')->alias("a")->join($join)->where($where)
		->order("createtime desc")
		->limit($Page->firstRow.','.$Page->listRows)
		->select();
		$this->assign('page',$show);
		$this->assign("comments",$comments);
		$this->display("User:comment");
	}
	//发表评论
	function runcomment(){
        if(!C('COMMENT.T_OPEN')){
            $this->error("未开启评论功能！",0,0);
        }
		$this->check_login();
        $this->check_last_action(C('COMMENT.T_LIMIT'));//评论间隔时间
        $comments_model=M('comments');
		if (IS_POST){
            $data['t_name']='news';
            $data['t_id']=I('n_id');
            $data['uid']=$this->user['member_list_id'];
            $data['to_uid']=I('to_uid',0,'intval');
            $data['full_name']=$this->user['member_list_username'];
            $data['email']=$this->user['member_list_email'];
            $data['createtime']=time();
            $data['c_content']=I('c_content');
            $data['parentid']=I('parentid',0,'intval');
			if ($comments_model->create()){
				$result=$comments_model->add($data);
				if ($result!==false){					
					//评论计数
					$table_model=M($data['t_name']);
					$pk=$table_model->getPk();
					$table_model->create(array("comment_count"=>array("exp","comment_count+1")));
					$table_model->where(array($pk=>$data['t_id']))->save();
                    //更新path字段
                    if($data['parentid']){
                        $rst=$comments_model->find($data['parentid']);
                        $path=$rst['path'].'-'.$result;
                    }else{
                        $path='0-'.$result;
                    }
                    $comments_model->where(array('c_id'=>$result))->setField('path',$path);
					$this->ajaxReturn(array("id"=>$result,'user'=>$this->user,'info'=>'评论成功！','status'=>1));
				} else {
					$this->error("评论失败！",0,0);
				}
			} else {
				$this->error($comments_model->getError(),0,0);
			}
		}else{
            $this->error("提交方式不正确！",0,0);
        }
	}
}