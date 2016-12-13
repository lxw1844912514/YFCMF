<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;
use think\Db;
/**
 * 文章列表
*/
class Listn extends Base {

	public function index() {
		$list_id=input('id');
		$page=input('page');
		$pagesize=5;
		$menu=Db::name('menu')->find(input('id'));
		if(empty($menu)){
			$this->error(lang('operation not valid'));
		}
		$tplname=$menu['menu_listtpl'];
		$tplname=$tplname?$tplname:'list';
		if($tplname=="photo_list") $pagesize=4;//相册格式
		if(request()->isAjax()){
			$lists=get_news('cid:'.$list_id.';order:news_time desc',1,$pagesize,null,null,array(),$page);
			//替换成带ajax的class
			$page_html=$lists['page'];
			$page_html=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$page_html);

			$this->assign('page_html',$page_html);
			$this->assign('lists',$lists);
			$this->assign('list_id', $list_id);
			return $this->view->fetch(":ajax_".$tplname);
		}else{
			$lists=get_news('cid:'.$list_id.';order:news_time desc',1,$pagesize);

			//替换成带ajax的class
			$page_html=$lists['page'];
			$page_html=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$page_html);

			$this->assign('menu',$menu);
			$this->assign('page_html',$page_html);
			$this->assign('lists',$lists);
			$this->assign('list_id', $list_id);
			return $this->view->fetch(":$tplname");
		}
	}
    public function search() {
		$k = input("keyword");
		$page = input("post.page");
		$pagesize=5;
		if (empty($k)) {
			$this -> error(lang('keywords empty'));
		}
		if(request()->isAjax()){
 			if(empty($page)){
				$this->success(lang('success'),url('search',array('keyword'=>$k)));
			}else{
				$lists=get_news('order:news_time desc',1,$pagesize,'keyword',$k,array(),$page);
				//替换成带ajax的class
				$page_html=$lists['page'];
				$page_html=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$page_html);
				$this->assign('page_html',$page_html);
				$this->assign('lists',$lists);
				$this -> assign("keyword", $k);
				return $this->view->fetch(":ajax_list");				
			} 
		}else{
			$lists=get_news('order:news_time desc',1,$pagesize,'keyword',$k);
			//替换成带ajax的class
			$page_html=$lists['page'];
			$page_html=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$page_html);
			$this->assign('page_html',$page_html);
			$this->assign('lists',$lists);
			$this -> assign("keyword", $k);		
			return $this->view->fetch(':search');
		}
    }
}
