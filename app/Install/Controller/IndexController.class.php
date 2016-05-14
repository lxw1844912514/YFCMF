<?php
namespace Install\Controller;
use Think\Controller;
use Think\Db;
/**
 * 首页
 */
class IndexController extends Controller {
    //判断是否已安装
    function _initialize(){
        if(file_exists_case("./data/install.lock")){
            redirect(__ROOT__."/");
        }
    }
    //首页
	public function index() {
    	$this->display(':index');
    }
    public function step2(){
        $data=array();
        $data['phpversion'] = @ phpversion();
        $data['os']=PHP_OS;
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $data['allow_url_fopen'] = ini_get('allow_url_fopen') ? '<i class="fa fa-check correct"></i> 已开启' : '<i class="fa fa-remove error"></i> 未开启';
        $safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
        }

        if (class_exists('pdo')) {
            $data['pdo'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['pdo'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }

        if (extension_loaded('pdo_mysql')) {
            $data['pdo_mysql'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['pdo_mysql'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }
        if (extension_loaded('exif')) {
            $data['exif'] = '<i class="fa fa-check correct"></i> 已开启';
        } else {
            $data['exif'] = '<i class="fa fa-remove error"></i> 未开启';
            $err++;
        }
        if (ini_get('file_uploads')) {
            $data['upload_size'] = '<i class="fa fa-check correct"></i> ' . ini_get('upload_max_filesize');
        } else {
            $data['upload_size'] = '<i class="fa fa-remove error"></i> 禁止上传';
        }

        if (function_exists('session_start')) {
            $data['session'] = '<i class="fa fa-check correct"></i> 支持';
        } else {
            $data['session'] = '<i class="fa fa-remove error"></i> 不支持';
            $err++;
        }

        $folders = array(
            '',//根目录
            'data',
            'data/conf',
            'data/runtime',
            'data/runtime/Cache',
            'data/runtime/Data',
            'data/runtime/Logs',
            'data/runtime/Temp',
            'data/upload',
        );
        $new_folders=array();
        foreach($folders as $dir){
            $Testdir = "./".$dir;
            create_dir($Testdir);
            if(testwrite($Testdir)){
                $new_folders[$dir]['w']=true;
            }else{
                $new_folders[$dir]['w']=false;
                $err++;
            }
            if(is_readable($Testdir)){
                $new_folders[$dir]['r']=true;
            }else{
                $new_folders[$dir]['r']=false;
                $err++;
            }
        }
        $data['folders']=$new_folders;

        $this->assign($data);
        $this->display(":step2");
    }
    public function step3(){
        $this->display(":step3");
    }
    public function step4(){
        if(IS_POST){
            //创建数据库
            $dbconfig['DB_TYPE']="mysql";
            $dbconfig['DB_HOST']=I('post.dbhost');
            $dbconfig['DB_USER']=I('post.dbuser');
            $dbconfig['DB_PWD']=I('post.dbpw');
            $dbconfig['DB_PORT']=I('post.dbport');
            $db  = Db::getInstance($dbconfig);
            $dbname=strtolower(I('post.dbname'));
            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
            $db->execute($sql) || $this->error($db->getError());
            $this->display(":step4");

            //创建数据表
            $dbconfig['DB_NAME']=$dbname;
            $dbconfig['DB_PREFIX']=trim(I('post.dbprefix'));
            $db  = Db::getInstance($dbconfig);
            $table_prefix=I("post.dbprefix");
            execute_sql($db, "yfcmf.sql", $table_prefix);

            //更新配置信息
            update_site_configs($db, $table_prefix);
            //创建管理员
            create_admin_account($db, $table_prefix);
            //生成网站配置文件
            create_config($dbconfig);
            session("_install_step",4);
            sleep(1);
            $this->redirect("step5");
        }else{
            exit;
        }
    }
    public function step5(){
        if(session("_install_step")==4){
            @touch('./data/install.lock');
            $this->display(":step5");
        }else{
            $this->error("非法安装！");
        }
    }
    public function testdbpwd(){
        if(IS_POST){
            $dbconfig=I("POST.");
            $dbconfig['DB_TYPE']="mysql";
            $db  = Db::getInstance($dbconfig);
            try{
                $db->query("show databases;");
            }catch (\Exception $e){
                die("");
            }
            exit("1");
        }else{
            exit("need post!");
        }
    }
}

