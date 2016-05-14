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
}
