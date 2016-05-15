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
class NewsController extends HomebaseController {
    //文章内页
    public function index() {
		$join = "".C('DB_PREFIX').'admin as b on a.news_auto =b.admin_id';
		$news=M('news')->alias("a")->join($join)->where(array('n_id'=>I('id'),'news_open'=>1,'news_back'=>0))->find();
		if(empty($news)){
		    $this->error('此操作无效');
		}
		$menu=M('menu')->find($news['news_columnid']);
		if(empty($menu)){
		    $this->error('此操作无效');
		}
		$tplname=$menu['menu_newstpl'];
    	$tplname=$tplname?$tplname:'news';
		//自行根据网站需要考虑，是否需要判断
		$can_do=check_user_action('news'.I('id'),0,false,60);
		if($can_do){
			//更新点击数
			M('news')->save(array("n_id"=>I('id'),"news_hits"=>array("exp","news_hits+1")));
			$news['news_hits']+=1;
		}
		$next=M('news')->where(array("news_time"=>array("egt",$news['news_time']), "n_id"=>array('neq',I('id')),"news_open"=>1,'news_back'=>0,'news_columnid'=>$news['news_columnid']))->order("news_time asc")->find();
		$prev=M('news')->where(array("news_time"=>array("elt",$news['news_time']), "n_id"=>array('neq',I('id')), "news_open"=>1,'news_back'=>0,'news_columnid'=>$news['news_columnid']))->order("news_time desc")->find();
		$t_open=C('COMMENT.T_OPEN');
        if($t_open){
            //获取评论数据
            $join = "".C('DB_PREFIX').'member_list as b on a.uid =b.member_list_id';
            $comment_model=M('comments');
            $comments=$comment_model->alias("a")->join($join)->where(array("a.t_name"=>'news',"a.t_id"=>I('id'),"a.c_status"=>1))->order("a.createtime ASC")->select();
            $count=count($comments);
            $new_comments=array();
            $parent_comments=array();
            if(!empty($comments)){
                foreach ($comments as $m){
                    if($m['parentid']==0){
                        $new_comments[$m['c_id']]=$m;
                    }else{
                        $path=explode("-", $m['path']);
                        $new_comments[$path[1]]['children'][]=$m;
                    }
                    $parent_comments[$m['c_id']]=$m;
                }
            }
            $this->assign("count",$count);
            $this->assign("comments",$new_comments);
            $this->assign("parent_comments",$parent_comments);
        }
        $this->assign("t_open",$t_open);
		$this->assign($news);
		$this->assign("next",$next);
    	$this->assign("prev",$prev);
    	$this->display(":$tplname");
    }
    
    public function dolike(){
	    $this->check_login();
    	$id=intval($_GET['id']);
    	$news_model=M("news");
    	$can_like=check_user_action('news'.$id,1);
    	if($can_like){
    		$news_model->save(array("n_id"=>$id,"news_like"=>array("exp","news_like+1")));
    		$this->success("赞好啦！",1,1);
    	}else{
    		$this->error("您已赞过啦！",0,0);
    	}
    }
	function dofavorite(){
        $this->check_login();
		$key=I('key');
		if($key){
			$id=I('id');
			if($key==encrypt_password('news-'.$id,'news')){
				$uid=session('hid');
				$favorites_model=M("favorites");
				$find_favorite=$favorites_model->where(array('t_name'=>'news','t_id'=>$id,'uid'=>$uid))->find();
				if($find_favorite){
					$this->error("亲，您已收藏过啦！",0,0);
				}else {
                    $data=array(
                        'uid'=>$uid,
                        't_name'=>'news',
                        't_id'=>$id,
                        'createtime'=>time(),
                    );
					$result=$favorites_model->add($data);
					if($result){
						$this->success("收藏成功！",1,1);
					}else {
						$this->error("收藏失败！",0,0);
					}
				}
			}else{
				$this->error("非法操作，无合法密钥！",0,0);
			}
		}else{
			$this->error("非法操作，无密钥！",0,0);
		}
	}
}
