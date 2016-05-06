<?php
namespace Home\Controller;
use Home\Controller\HomebaseController;
/**
 * 文章列表
*/
class ListController extends HomebaseController {

	public function index() {
		$menu=M('menu')->find(I('id'));
		if(empty($menu)){
		    $this->error('此操作无效');
		}
		$tplname=$menu['menu_listtpl'];
    	$tplname=$tplname?$tplname:'list';
    	$this->assign('menu',$menu);
    	$this->assign('list_id', I('id'));
    	$this->display(":$tplname");
	}
	
	public function nav_index(){		
	}
	public function load_more(){	
	}
}
