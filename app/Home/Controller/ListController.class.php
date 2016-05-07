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
    public function search() {
		$k = I("keyword");
		if (empty($k)) {
			$this -> error("关键词不能为空！请重新输入！");
		}
		$this -> assign("keyword", $k);
		$this -> display(":search");
    }
}
