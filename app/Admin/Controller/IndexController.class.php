<?php
namespace Admin\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController {
	//首页
	public function index(){
		//未登录
		if (empty($_SESSION['aid'])){
			$this->redirect('Login/login');
		}
		//系统信息
		$info = array(
			'PCTYPE'=>PHP_OS,
			'RUNTYPE'=>$_SERVER["SERVER_SOFTWARE"],
			'ONLOAD'=>ini_get('upload_max_filesize'),
			'ThinkPHPTYE'=>THINK_VERSION,
		);
		$this->assign('info',$info);

		$news=M('news');
		//$start=strtotime(date('Y-m-01 00:00:00'));
		//$end = strtotime(date('Y-m-d H:i:s'));
		//$data['news_time'] = array('between',array($start,$end));
		//热门文章排行
		$news_list=$news->order('news_hits desc')->limit(0,10)->select();
		$this->assign('news_list',$news_list);
		//总文章数
		$news_count=$news->count();
		$this->assign('news_count',$news_count);

		$today=strtotime(date('Y-m-d 00:00:00'));//今天开始日期
		$todata['news_time'] = array('egt',$today);
		//今日发表文章数
		$tonews_count=$news->where($todata)->count();
		$this->assign('tonews_count',$tonews_count);

		$ztday=strtotime(date('Y-m-d 00:00:00'))-60*60*24;//昨天开始日期
		$ztdata['news_time'] = array('between',array($ztday,$today));
		//昨日文章数
		$ztnews_count=$news->where($ztdata)->count();
		$this->assign('ztnews_count',$ztnews_count);
		$difday=($ztnews_count>0)?($tonews_count-$ztnews_count)/$ztnews_count*100:0;
		//今日提升比
		$this->assign('difday',$difday);
		
		//安全检测
		$this->system_safe = true;

        $this->danger_mode_debug = APP_DEBUG;
        if ($this->danger_mode_debug) {
            $this->system_safe = false;
        }

        $this->weak_setting_db_password = false;
        $weak_pwd_reg = array(
            '/^[0-9]{0,6}$/',
            '/^[a-z]{0,6}$/',
            '/^[A-Z]{0,6}$/'
        );
        foreach ($weak_pwd_reg as $reg) {
            if (preg_match($reg, C('DB_PWD'))) {
                $this->weak_setting_db_password = true;
                break;
            }
        }
        if ($this->weak_setting_db_password) {
            $this->system_safe = false;
        }
        $this->weak_setting_admin_password = session('admin_weak_pwd');
        if ($this->weak_setting_admin_password) {
            $this->system_safe = false;
        }
        $this->weak_setting_admin_last_change_password = (session('admin_last_change_pwd_time') < time() - 3600 * 24 * 30);
        if ($this->weak_setting_admin_last_change_password) {
            $this->system_safe = false;
        }
		//渲染模板
		$this->display();
	}
}