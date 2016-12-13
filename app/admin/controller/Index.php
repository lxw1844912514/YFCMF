<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Db;
use think\Cache;
use think\helper\Time;
class Index extends Base
{
	public function index(){
		//未登录
		$aid_s=session('aid');
		if (empty($aid_s)){
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
		//热门文章排行
		$news_list=Db::name('news')->where('news_l',$this->lang)->order('news_hits desc')->limit(0,10)->select();
		$this->assign('news_list',$news_list);
		//总文章数
		$news_count=Db::name('news')->count();
		$this->assign('news_count',$news_count);
        //总会员数
        $members_count=Db::name('member_list')->count();
        $this->assign('members_count',$members_count);
        //总留言数
        $sugs_count=Db::name('plug_sug')->count();
        $this->assign('sugs_count',$sugs_count);
        //总评论数
        $coms_count=Db::name('comments')->count();
        $this->assign('coms_count',$coms_count);
		
		//日期时间戳
		list($start_t, $end_t) = Time::today();
		list($start_y, $end_y) = Time::yesterday();

		//今日发表文章数
		$tonews_count=Db::name('news')->whereTime('news_time', 'between', [$start_t, $end_t])->count();
		$this->assign('tonews_count',$tonews_count);

		//昨日文章数
		$ztnews_count=Db::name('news')->whereTime('news_time', 'between', [$start_y, $end_y])->count();
		$this->assign('ztnews_count',$ztnews_count);
		//今日提升比
		$difday=($ztnews_count>0)?($tonews_count-$ztnews_count)/$ztnews_count*100:0;
		$this->assign('difday',$difday);
		
		//今日增加会员
        $tomembers_count=Db::name('member_list')->whereTime('member_list_addtime', 'between', [$start_t, $end_t])->count();
        $this->assign('tomembers_count',$tomembers_count);
        //昨日会员数
        $ztmembers_count=Db::name('member_list')->whereTime('member_list_addtime', 'between', [$start_y, $end_y])->count();
        $this->assign('ztmembers_count',$ztmembers_count);
		//今日提升比
        $difday_m=($ztmembers_count>0)?($tomembers_count-$ztmembers_count)/$ztmembers_count*100:0;
        $this->assign('difday_m',$difday_m);
		
        //今日留言
        $tosugs_count=Db::name('plug_sug')->whereTime('plug_sug_addtime', 'between', [$start_t, $end_t])->count();
        $this->assign('tosugs_count',$tosugs_count);
		//昨日留言
        $ztsugs_count=Db::name('plug_sug')->whereTime('plug_sug_addtime', 'between', [$start_y, $end_y])->count();
        $this->assign('ztsugs_count',$ztsugs_count);
		//今日提升比
        $difday_s=($ztsugs_count>0)?($tosugs_count-$ztsugs_count)/$ztsugs_count*100:0;
        $this->assign('difday_s',$difday_s);
		
        //今日评论
        $tocoms_count=Db::name('comments')->whereTime('createtime', 'between', [$start_t, $end_t])->count();
        $this->assign('tocoms_count',$tocoms_count);
		//昨日评论
        $ztcoms_count=Db::name('comments')->whereTime('createtime', 'between', [$start_y, $end_y])->count();
        $this->assign('ztcoms_count',$ztcoms_count);
		//今日提升比
        $difday_c=($ztcoms_count>0)?($tocoms_count-$ztcoms_count)/$ztcoms_count*100:0;
        $this->assign('difday_c',$difday_c);

		//安全检测
 		$system_safe = true;
		//调试模式
        $danger_mode_debug = config('app_debug');
        if ($danger_mode_debug) {
            $system_safe = false;
        }
		$this->assign('danger_mode_debug',$danger_mode_debug);

		//数据库密码
        $weak_setting_db_password = false;
        $weak_pwd_reg = array(
            '/^[0-9]{0,6}$/',
            '/^[a-z]{0,6}$/',
            '/^[A-Z]{0,6}$/'
        );
        foreach ($weak_pwd_reg as $reg) {
            if (preg_match($reg, config('database.password'))) {
                $weak_setting_db_password = true;
                break;
            }
        }
        if ($weak_setting_db_password) {
            $system_safe = false;
        }
		$this->assign('weak_setting_db_password',$weak_setting_db_password);
		
		//管理员密码
        $weak_setting_admin_password = session('admin_weak_pwd');
        if ($weak_setting_admin_password) {
            $system_safe = false;
        }
		$this->assign('weak_setting_admin_password',$weak_setting_admin_password);

		//密码修改时间
        $weak_setting_admin_last_change_password = (session('admin_last_change_pwd_time') < time() - 3600 * 24 * 30);
        if ($weak_setting_admin_last_change_password) {
            $system_safe = false;
        }
		$this->assign('weak_setting_admin_last_change_password',$weak_setting_admin_last_change_password);
		
		//整体安全性
		$this->assign('system_safe',$system_safe);

		//页面调试
 		$this->assign('system_pageshow',config('app_trace'));

		//日志分析
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
        $ver_curr=substr(config('yfcmf_version'),1);
        $update_check=config('update_check');
        $this->assign('update_check',$update_check);
        $ver_str='';
        $ver_last='';
        if($update_check){
            //版本检查
            $version=cache('ver_last');
            if(empty($version)){
                $version = checkVersion();
                cache('ver_last',$version);
            }
            $ver_last=substr($version,1);
            if(version_compare($ver_curr,$ver_last)===-1){
                $ver_str='最新版本V'.$ver_last;
            }else{
                $ver_str='已经是最新版本';
                $ver_last='';
            }
        }
        $this->assign('ver_str',$ver_str);
        $this->assign('ver_last',$ver_last);
		//渲染模板
        return $this->fetch();
	}
	public function lang()
	{
		if (!request()->isAjax()){
			$this->error('提交方式不正确');
		}else{
			$lang=input('lang_s');
			session('login_http_referer',$_SERVER["HTTP_REFERER"]);
			switch ($lang) {
				case 'cn':
					cookie('think_var', 'zh-cn');
				break;
				case 'en':
					cookie('think_var', 'en-us');
				break;
				//其它语言
				default:
					cookie('think_var', 'zh-cn');
			}
			Cache::clear();
			$this->success('切换成功',session('login_http_referer'));
		}
	}
}