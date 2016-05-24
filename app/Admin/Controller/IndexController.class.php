<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\CommonController;
class IndexController extends CommonController {
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
		//热门文章排行
		$news_list=$news->order('news_hits desc')->limit(0,10)->select();
		$this->assign('news_list',$news_list);
		//总文章数
		$news_count=$news->count();
		$this->assign('news_count',$news_count);
        //总会员数
        $members_count=M('member_list')->count();
        $this->assign('members_count',$members_count);
        //总留言数
        $sugs_count=M('plug_sug')->count();
        $this->assign('sugs_count',$sugs_count);
        //总评论数
        $coms_count=M('comments')->count();
        $this->assign('coms_count',$coms_count);

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
		//今日增加会员
        $tomembers_count=M('member_list')->where(array('member_list_addtime'=>array('egt',$today)))->count();
        $this->assign('tomembers_count',$tomembers_count);
        //昨日会员数
        $ztmembers_count=M('member_list')->where(array('member_list_addtime'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztmembers_count',$ztmembers_count);
        $difday_m=($ztmembers_count>0)?($tomembers_count-$ztmembers_count)/$ztmembers_count*100:0;
        $this->assign('difday_m',$difday_m);
        //今日留言
        $tosugs_count=M('plug_sug')->where(array('plug_sug_addtime'=>array('egt',$today)))->count();
        $this->assign('tosugs_count',$tosugs_count);
        $ztsugs_count=M('plug_sug')->where(array('plug_sug_addtime'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztsugs_count',$ztsugs_count);
        $difday_s=($ztsugs_count>0)?($tosugs_count-$ztsugs_count)/$ztsugs_count*100:0;
        $this->assign('difday_s',$difday_s);
        //今日评论
        $tocoms_count=M('comments')->where(array('createtime'=>array('egt',$today)))->count();
        $this->assign('tocoms_count',$tocoms_count);
        $ztcoms_count=M('comments')->where(array('createtime'=>array('between',array($ztday,$today))))->count();
        $this->assign('ztcoms_count',$ztcoms_count);
        $difday_c=($ztcoms_count>0)?($tocoms_count-$ztcoms_count)/$ztcoms_count*100:0;
        $this->assign('difday_c',$difday_c);
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
		$debug=APP_DEBUG;
		$this->assign('debug',$debug);
		$log_size = 0;
        $log_file_cnt = 0;
        foreach (list_file(LOG_PATH) as $f) {
            if ($f ['isDir']) {
                foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
                    if ($ff ['isFile']) {
                        $log_size += $ff ['size'];
                        $log_file_cnt++;
                    }
                }
            }
        }
		$this->assign('log_size',$log_size);
		$this->assign('log_file_cnt',$log_file_cnt);
		//渲染模板
		$this->display();
	}
}