<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use think\Controller;
use think\Request;
use think\Lang;
class Common extends Controller{
    // Request实例
    protected $request;
	protected $lang;
	protected function _initialize(){
        if (!defined('__ROOT__')) {
            $_root = rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'], '/')), '/');
            define('__ROOT__', (('/' == $_root || '\\' == $_root) ? '' : $_root));
        }
		if (!file_exists(ROOT_PATH.'data/install.lock')) {
            //不存在，则进入安装
            header('Location: ' . url('install/index/index'));
            exit();
        }
		if (null === $this->request) {
            $this->request = Request::instance();
        }
        if (!defined('MODULE_NAME')){define('MODULE_NAME', $this->request->module());}
        if (!defined('CONTROLLER_NAME')){define('CONTROLLER_NAME', $this->request->controller());}
        if (!defined('ACTION_NAME')){define('ACTION_NAME', $this->request->action());}
		// 多语言
		if(config('lang_switch_on')){
			$this->lang=Lang::detect();
		}else{
			$this->lang=config('default_lang');
		}
		$this->assign('lang',$this->lang);
	}
    //空操作
    public function _empty(){
        $this->error(lang('operation not valid'));
    }
}