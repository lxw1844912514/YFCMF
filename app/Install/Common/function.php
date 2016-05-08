<?php
use Org\Util\String;
/*
*	测试是否可写
*	rainfer <81818832@qq.com>
*/
function testwrite($d) {
    $tfile = "_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}
/*
*	建立文件夹
*	rainfer <81818832@qq.com>
*/
function create_dir($path) {
    if (is_dir($path))
        return true;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}
/*
*	返回路径
*	rainfer <81818832@qq.com>
*/
function dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}
/*
*	执行sql文件
*	rainfer <81818832@qq.com>
*/
function execute_sql($db,$file,$tablepre){
    //读取SQL文件
    $sql = file_get_contents(MODULE_PATH . 'Data/'.$file);
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);
    
    //替换表前缀
    $default_tablepre = "yf_";
    $sql = str_replace(" `{$default_tablepre}", " `{$tablepre}", $sql);
    
    //开始安装
    show_msg('开始安装数据库...');
    foreach ($sql as $item) {
        $item = trim($item);
        if(empty($item)) continue;
        preg_match('/CREATE TABLE `([^ ]*)`/', $item, $matches);
        if($matches) {
            $table_name = $matches[1];
            $msg  = "创建数据表{$table_name}";
            if(false !== $db->execute($item)){
                show_msg($msg . ' 完成');
            } else {
                show_msg($msg . ' 失败！', 'error');
            }
        } else {
            $db->execute($item);
        }
    
    }
}

/*
*	显示提示信息
*	rainfer <81818832@qq.com>
*/
function show_msg($msg, $class = ''){
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}
/*
*	更新系统设置
*	rainfer <81818832@qq.com>
*/
function update_site_configs($db,$table_prefix){
    $sitename=I("post.sitename");
    $email=I("post.manager_email");
    $siteurl=I("post.siteurl");
    $seo_keywords=I("post.sitekeywords");
    $seo_description=I("post.siteinfo");
    $site_options=<<<helllo
            {
            		"site_name":"$sitename",
            		"site_host":"$siteurl",
            		"site_root":"",
            		"site_logo":"./data/upload/2016-05-06/572c6855f09fc.png",
            		"site_icp":"",
            		"site_tpl":"Default",
            		"site_admin_email":"$email",
            		"site_tongji":"",
            		"site_copyright":"",
            		"site_seo_title":"$sitename",
            		"site_seo_keywords":"$seo_keywords",
            		"site_seo_description":"$seo_description"
        }
helllo;
    $sql="INSERT INTO `{$table_prefix}options` (option_value,option_name) VALUES ('$site_options','site_options')";
    $db->execute($sql);
    show_msg("网站信息配置成功!");
}
/*
*	创建管理员
*	rainfer <81818832@qq.com>
*/
function create_admin_account($db,$table_prefix){
    $username=I("post.manager");
	$admin_pwd_salt=String::randString(10);
    $password=encrypt_password(I("post.manager_pwd"),$admin_pwd_salt);
    $email=I("post.manager_email");
    $create_date=time();
    $ip=get_client_ip();
    $sql =<<<hello
    INSERT INTO `{$table_prefix}admin` 
    (admin_id, admin_username, admin_pwd, admin_pwd_salt,admin_changepwd, admin_email, admin_realname, admin_tel, admin_hits, admin_ip, admin_addtime, admin_mdemail, admin_open) VALUES
    ('1', '{$username}', '{$password}','{$admin_pwd_salt}','{$create_date}','{$email}', '','',1,'{$ip}', {$create_date}, '', 1);;
hello;
    $db->execute($sql);
    show_msg("管理员账号创建成功!");
}


/*
*	写入配置
*	rainfer <81818832@qq.com>
*/
function create_config($config){
    if(is_array($config)){
        //读取配置内容
        $conf = file_get_contents(MODULE_PATH . 'Data/config.php');
        //替换配置项
        foreach ($config as $key => $value) {
            $conf = str_replace("#{$key}#", $value, $conf);
        }
        //写入应用配置文件
        if(file_put_contents( 'data/conf/db.php', $conf)){
            show_msg('配置文件写入成功');
        } else {
            show_msg('配置文件写入失败！', 'error');
        }
        return '';
    }
}
