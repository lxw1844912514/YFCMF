<?php
/**
 * 文章内页
 */
namespace Home\Controller;
use Home\Controller\HomebaseController;
class NewsController extends HomebaseController {
    //文章内页
    public function index() {
		$news=M('news')->where(array('n_id'=>I('id'),'news_open'=>1,'news_back'=>0))->find();
		if(empty($news)){
		    $this->error('此操作无效');
		}
		$menu=M('menu')->find($news['news_columnid']);
		if(empty($menu)){
		    $this->error('此操作无效');
		}
		$tplname=$menu['menu_newstpl'];
    	$tplname=$tplname?$tplname:'news';
    	$this->display(":$tplname");
    }
    
    public function do_like(){
    }
}
