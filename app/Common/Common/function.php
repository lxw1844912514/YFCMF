<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
use Think\Db;
use Think\Storage;
/**
 * 发送邮件
 * @author rainfer <81818832@qq.com>
 */
function sendMail($to, $title, $content) {
	$email_options=get_email_options();
	if($email_options && $email_options['email_open']){
		Vendor('PHPMailer.PHPMailerAutoload');
		$mail = new PHPMailer(); //实例化
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		$mail->Mailer='smtp';
		$mail->IsHTML(true);
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		$mail->CharSet='UTF-8';
		// 添加收件人地址，可以多次使用来添加多个收件人
		$mail->AddAddress($to);
		// 设置邮件正文
		$mail->Body=$content;
		// 设置邮件头的From字段。
		$mail->From=$email_options['email_name'];
		// 设置发件人名字
		$mail->FromName=$email_options['email_rename'];
		// 设置邮件标题
		$mail->Subject=$title;
		// 设置SMTP服务器。
		$mail->Host=$email_options['email_smtpname'];
		//by Rainfer
		// 设置SMTPSecure。
		$mail->SMTPSecure=$email_options['smtpsecure'];
		// 设置SMTP服务器端口。
		$port=$email_options['smtp_port'];
		$mail->Port=empty($port)?"25":$port;
		// 设置为"需要验证"
		$mail->SMTPAuth=true;
		// 设置用户名和密码。
		$mail->Username=$email_options['email_emname'];
		$mail->Password=$email_options['email_pwd'];
		// 发送邮件。
		if(!$mail->Send()) {
			$mailerror=$mail->ErrorInfo;
			return array("error"=>1,"message"=>$mailerror);
		}else{
			return array("error"=>0,"message"=>"success");
		}
	}else{
		return array("error"=>1,"message"=>'未开启邮件发送或未配置');
	}
}

function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author rainfer <81818832@qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
/**
 * 递归重组节点信息为多维数组
 *
 * @param array $node
 * @param number $pid
 * @author rainfer <81818832@qq.com>
 */
function node_merge(&$node, $pid = 0, $id_name = 'id', $pid_name = 'pid', $child_name = '_child')
{
    $arr = array();

    foreach ($node as $v) {
        if ($v [$pid_name] == $pid) {
            $v [$child_name] = node_merge($node, $v [$id_name], $id_name, $pid_name, $child_name);
            $arr [] = $v;
        }
    }

    return $arr;
}
/**
 * 数据表导出excel
 *
 * @author rainfer <81818832@qq.com>
 *
 * @param string $table,不含前缀表名,必须
 * @param string $file,保存的excel文件名,默认表名为文件名
 * @param string $fields,需要导出的字段名,默认全部,以半角逗号隔开
 * @param string $field_titles,需要导出的字段标题,需与$field一一对应,为空则表示直接以字段名为标题,以半角逗号隔开
 * @param stting $tag,筛选条件 以字符串方式传入,例："limit:0,8;order:post_date desc,listorder desc;where:id>0;"
 *      limit:数据条数,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始)
 *      order:排序方式，如：post_date desc
 *      where:查询条件，字符串形式，和sql语句一样
 */
function export2excel($table,$file='',$fields='',$field_titles='',$tag=''){
    //处理传递的参数
    if(stripos($table,C('DB_PREFIX'))==0){
        //含前缀的表,去除表前缀
		$table=substr($table,strlen(C('DB_PREFIX')));
    }
    $file=empty($file)?C('DB_PREFIX').$table:$file;
    $fieldsall=M($table)->getDbFields();
    $field_titles=empty($field_titles)?array():explode(",",$field_titles);
    if(empty($fields)){
        $fields=$fieldsall;
        //成员数不一致,则取字段名为标题
        if(count($fields)!=count($field_titles)){
            $field_titles=$fields;
        }
    }else{
        $fields=explode(",",$fields);
        $rst=array();
        $rsttitle=array();
        $title_y_n=(count($fields)==count($field_titles))?true:false;
        foreach($fields as $k=>$v){
            if(in_array($v,$fieldsall)){
                $rst[]=$v;
                //一一对应则取指定标题,否则取字段名
                $rsttitle[]=$title_y_n?$field_titles[$k]:$v;
            }
        }
        $fields=$rst;
        $field_titles=$rsttitle;
    }
    //处理tag标签
    $tag=param2array($tag);
    $limit = !empty($tag['limit']) ? $tag['limit'] : '';
    $order = !empty($tag['order']) ? $tag['order'] : '';
    $where=array();
    if (!empty($tag['where'])) {
        $where['_string'] = $tag['where'];
    }
    //处理数据
    $data= M($table)->field(join(",",$fields))->where($where)->order($order)->limit($limit)->select();
    import("Org.Util.PHPExcel");
    error_reporting(E_ALL);
    date_default_timezone_set('Europe/London');
    $objPHPExcel = new \PHPExcel();
    import("Org.Util.PHPExcel.Reader.Excel5");
    /*设置excel的属性*/
    $objPHPExcel->getProperties()->setCreator("rainfer")//创建人
    ->setLastModifiedBy("rainfer")//最后修改人
    ->setKeywords("excel")//关键字
    ->setCategory("result file");//种类

    //第一行数据
    $objPHPExcel->setActiveSheetIndex(0);
    $active = $objPHPExcel->getActiveSheet();
    foreach($field_titles as $i=>$name){
        $ck = num2alpha($i++) . '1';
        $active->setCellValue($ck, $name);
    }
    //填充数据
    foreach($data as $k => $v){
        $k=$k+1;
        $num=$k+1;//数据从第二行开始录入
        $objPHPExcel->setActiveSheetIndex(0);
        foreach($fields as $i=>$name){
            $ck = num2alpha($i++) . $num;
            $active->setCellValue($ck, $v[$name]);
        }
    }
    $objPHPExcel->getActiveSheet()->setTitle($table);
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$file.'.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}
/**
 * 生成参数列表,以数组形式返回
 * @author rainfer <81818832@qq.com>
 */
function param2array($tag = ''){
    $param = array();
    $array = explode(';',$tag);
    foreach ($array as $v){
        $v=trim($v);
        if(!empty($v)){
            list($key,$val) = explode(':',$v);
            $param[trim($key)] = trim($val);
        }
    }
    return $param;
}
/**
 * 数字到字母列
 * @author rainfer <81818832@qq.com>
 */
function num2alpha($intNum, $isLower = false)
{
    $num26 = base_convert($intNum, 10, 26);
    $addcode = $isLower ? 49 : 17;
    $result = '';
    for ($i = 0; $i < strlen($num26); $i++) {
        $code = ord($num26{$i});
        if ($code < 58) {
            $result .= chr($code + $addcode);
        } else {
            $result .= chr($code + $addcode - 39);
        }
    }
    return $result;
}
/**
 * 返回不含前缀的数据库表数组
 *
 * @author rainfer <81818832@qq.com>
 *
 * @return array
 */
function db_get_tables()
{
    static $tables = null;
    if (null === $tables) {
        $db_prefix = C('DB_PREFIX');
        $db = Db::getInstance();
        $tables = array();
        foreach ($db->getTables() as $t) {
            if (strpos($t, $db_prefix) === 0) {
                $t = substr($t, strlen($db_prefix));
                $tables [] = strtolower($t);
            }
        }
    }
    return $tables;
}
/**
 * 返回数据表的sql
 *
 * @author rainfer <81818832@qq.com>
 * 
 * @param $table : 不含前缀的表名
 * @return string
 */
function db_get_insert_sqls($table)
{
    static $db = null;
    if (null === $db) {
        $db = Db::getInstance();
    }
    $db_prefix = C('DB_PREFIX');
    $db_prefix_re = preg_quote($db_prefix);
    $db_prefix_holder = db_get_db_prefix_holder();
    $export_sqls = array();
    $export_sqls [] = "DROP TABLE IF EXISTS $db_prefix_holder$table";

    switch (C('DB_TYPE')) {
        case 'mysql' :
            if (!($d = $db->query("SHOW CREATE TABLE $db_prefix$table"))) {
                $this->error("'SHOW CREATE TABLE $table' Error!");
            }
            $table_create_sql = $d [0] ['create table'];
            $table_create_sql = preg_replace('/' . $db_prefix_re . '/', $db_prefix_holder, $table_create_sql);
            $export_sqls [] = $table_create_sql;
            $data_rows = $db->query("SELECT * FROM $db_prefix$table");
            $data_values = array();
            foreach ($data_rows as &$v) {
                foreach ($v as &$vv) {
                    $vv = "'" . mysql_escape_string($vv) . "'";
                }
                $data_values [] = '(' . join(',', $v) . ')';
            }
            if (count($data_values) > 0) {
                $export_sqls [] = "INSERT INTO `$db_prefix_holder$table` VALUES \n" . join(",\n", $data_values);
            }
            break;
    }

    return join(";\n", $export_sqls) . ";";
}
/**
 * 检测当前数据库中是否含指定表
 *
 * @author rainfer <81818832@qq.com>
 *
 * @param $table : 不含前缀的数据表名
 * @return bool
 */
function db_is_valid_table_name($table)
{
    return in_array($table, db_get_tables());
}
/**
 * 不检测表前缀,恢复数据库
 *
 * @author rainfer <81818832@qq.com>
 *
 * @param $file
 */
function db_restore_file($file)
{
    static $db = null;
    static $db_prefix = null;
    if (null === $db) {
        $db = Db::getInstance();
        $db_prefix = C('DB_PREFIX');
    }
    $sqls = file_get_contents($file);
    $sqls = str_replace(db_get_db_prefix_holder(), $db_prefix, $sqls);
    $sqlarr = explode(";\n", $sqls);
    foreach ($sqlarr as &$sql) {
        $db->execute($sql);
    }
}
/**
 * 返回表前缀替代符
 * @author rainfer <81818832@qq.com>
 *
 * @return string
 */
function db_get_db_prefix_holder()
{
    return '<--db-prefix-->';
}
/**
 * 强制下载
 * @author rainfer <81818832@qq.com>
 *
 * @param string $filename
 */
function force_download_content($filename, $content)
{
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Transfer-Encoding: binary");
    header("Content-Disposition: attachment; filename=$filename");
    echo $content;
    exit ();
}
/**
 * 所有用到密码的不可逆加密方式
 * @author rainfer <81818832@qq.com>
 *
 * @param string $password
 * @param string $password_salt
 * @return string
 */
function encrypt_password($password, $password_salt)
{
    return md5(md5($password) . md5($password_salt));
}
/**
 * 列出本地目录的文件
 * @author rainfer <81818832@qq.com>
 *
 * @param string $filename
 * @param string $pattern
 * @return Array
 */
function list_file($filename, $pattern = '*')
{
    if (strpos($pattern, '|') !== false) {
        $patterns = explode('|', $pattern);
    } else {
        $patterns [0] = $pattern;
    }
    $i = 0;
    $dir = array();
    if (is_dir($filename)) {
        $filename = rtrim($filename, '/') . '/';
    }
    foreach ($patterns as $pattern) {
        $list = glob($filename . $pattern);
        if ($list !== false) {
            foreach ($list as $file) {
                $dir [$i] ['filename'] = basename($file);
                $dir [$i] ['path'] = dirname($file);
                $dir [$i] ['pathname'] = realpath($file);
                $dir [$i] ['owner'] = fileowner($file);
                $dir [$i] ['perms'] = substr(base_convert(fileperms($file), 10, 8), -4);
                $dir [$i] ['atime'] = fileatime($file);
                $dir [$i] ['ctime'] = filectime($file);
                $dir [$i] ['mtime'] = filemtime($file);
                $dir [$i] ['size'] = filesize($file);
                $dir [$i] ['type'] = filetype($file);
                $dir [$i] ['ext'] = is_file($file) ? strtolower(substr(strrchr(basename($file), '.'), 1)) : '';
                $dir [$i] ['isDir'] = is_dir($file);
                $dir [$i] ['isFile'] = is_file($file);
                $dir [$i] ['isLink'] = is_link($file);
                $dir [$i] ['isReadable'] = is_readable($file);
                $dir [$i] ['isWritable'] = is_writable($file);
                $i++;
            }
        }
    }
    $cmp_func = create_function('$a,$b', '
		if( ($a["isDir"] && $b["isDir"]) || (!$a["isDir"] && !$b["isDir"]) ){
			return  $a["filename"]>$b["filename"]?1:-1;
		}else{
			if($a["isDir"]){
				return -1;
			}else if($b["isDir"]){
				return 1;
			}
			if($a["filename"]  ==  $b["filename"])  return  0;
			return  $a["filename"]>$b["filename"]?-1:1;
		}
		');
    usort($dir, $cmp_func);
    return $dir;
}
/**
 * 删除文件夹
 * @author rainfer <81818832@qq.com>
 *
 */
function remove_dir($dir, $time_thres = -1)
    {
        foreach (list_file($dir) as $f) {
            if ($f ['isDir']) {
                remove_dir($f ['pathname'] . '/');
            } else if ($f ['isFile'] && $f ['filename'] != C('DIR_SECURE_FILENAME')) {
                if ($time_thres == -1 || $f ['mtime'] < $time_thres) {
                    @unlink($f ['pathname']);
                }
            }
        }
    }
/**
 * 将内容存到Storage中，返回转存后的文件路径
 * @author rainfer <81818832@qq.com>
 *
 * @param string $dir
 * @param string $ext
 * @param string $content
 * @return string
 */
function save_storage_content($ext = null, $content = null, $filename = '')
{
    $newfile = '';
	$path=C('UPLOAD_DIR');
	$path=substr($path,0,2)=='./' ? substr($path,2) :$path;
	$path=substr($path,0,1)=='/' ? substr($path,1) :$path;
    if ($ext && $content) {
        do {
            $newfile = $path.date('Y-m-d/') . uniqid() . '.' . $ext;
        } while (Storage::has($newfile));
        Storage::put($newfile, $content);
    }
    return $newfile;
}
/**
 * 返回带协议的域名
 * @author rainfer <81818832@qq.com>
 */
function get_host(){
	$host=$_SERVER["HTTP_HOST"];
	$protocol=is_ssl()?"https://":"http://";
	return $protocol.$host;
}
/**
 * 获取后台管理设置的网站信息，此类信息一般用于前台
 * @author rainfer <81818832@qq.com>
 */
function get_site_options(){
	$site_options = F("site_options");
	if(empty($site_options)){
		$options_obj = M("Options");
		$option = $options_obj->where("option_name='site_options'")->find();
		if($option){
			$site_options = json_decode($option['option_value'],true);
			$site_options['site_copyright']=htmlspecialchars_decode($site_options['site_copyright']);
		}else{
			$site_options = array();
		}
		F("site_options", $site_options);
	}
	$site_options['site_tongji']=htmlspecialchars_decode($site_options['site_tongji']);
	return $site_options;	
}
/**
 * 获取后台管理设置的邮件连接
 * @author rainfer <81818832@qq.com>
 */
function get_email_options(){
	$email_options = F("email_options");
	if(empty($email_options)){
		$options_obj = M("Options");
		$option = $options_obj->where("option_name='email_options'")->find();
		if($option){
			$email_options = json_decode($option['option_value'],true);
		}else{
			$email_options = array();
		}
		F("email_options", $email_options);
	}
	return $email_options;	
}
/**
 * 获取后台管理设置的邮件激活连接
 * @author rainfer <81818832@qq.com>
 */
function get_active_options(){
	$active_options = F("active_options");
	if(empty($active_options)){
		$options_obj = M("Options");
		$option = $options_obj->where("option_name='active_options'")->find();
		if($option){
			$active_options = json_decode($option['option_value'],true);
		}else{
			$active_options = array();
		}
		F("active_options", $active_options);
	}
	return $active_options;	
}
/**
 * 获取所有友情连接
 * @author rainfer <81818832@qq.com>
 *
 * @return array
 */
function get_links($type=1){
	$links_obj= M("plug_link");
	return $links_obj->where(array('plug_link_typeid'=>$type,'plug_link_open'=>1))->order("plug_link_order ASC")->select();
}
/**
 * 返回指定id的菜单
 * @author rainfer <81818832@qq.com>
 * 
 * 同上一类方法，jquery treeview 风格，可伸缩样式
 * @param $myid 表示获得这个ID下的所有子级
 * @param $effected_id 需要生成treeview目录数的id
 * @param $str 末级样式
 * @param $str2 目录级别样式
 * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
 * @param $ul_class 内部ul样式 默认空  可增加其他样式如'sub-menu'
 * @param $li_class 内部li样式 默认空  可增加其他样式如'menu-item'
 * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
 * @param $dropdown 有子元素时li的class
 * $id="main";
 $effected_id="mainmenu";
 $filetpl="<a href='\$href'><span class='file'>\$label</span></a>";
 $foldertpl="<span class='folder'>\$label</span>";
 $ul_class="" ;
 $li_class="" ;
 $style="filetree";
 $showlevel=6;
 get_menu($id,$effected_id,$filetpl,$foldertpl,$ul_class,$li_class,$style,$showlevel);
 * such as
 * <ul id="example" class="filetree ">
 <li class="hasChildren" id='1'>
 <span class='folder'>test</span>
 <ul>
 <li class="hasChildren" id='4'>
 <span class='folder'>caidan2</span>
 <ul>
 <li class="hasChildren" id='5'>
 <span class='folder'>sss</span>
 <ul>
 <li id='3'><span class='folder'>test2</span></li>
 </ul>
 </li>
 </ul>
 </li>
 </ul>
 </li>
 <li class="hasChildren" id='6'><span class='file'>ss</span></li>
 </ul>
 */

function get_menu($id="main",$effected_id="mainmenu",$childtpl="<span class='file'>\$label</span>",$parenttpl="<span class='folder'>\$label</span>",$ul_class="" ,$li_class="" ,$style="filetree",$showlevel=6,$dropdown='hasChild'){
	$navs=F("site_nav_".$id);
	if(empty($navs)){
		$navs=get_menu_datas($id);
	}
	import("Org.Util.Tree");
	$tree = new \Tree();
	$tree->init($navs);
	return $tree->get_treeview_menu(0,$effected_id, $childtpl, $parenttpl,  $showlevel,$ul_class,$li_class,  $style,  1, FALSE, $dropdown);
}


function get_menu_datas($id){
	$nav_obj= M("menu");
	$navs= $nav_obj->where(array('menu_open'=>1))->order(array("listorder" => "ASC"))->select();
	foreach ($navs as $key=>$nav){
		if($nav['menu_type']==2){
			$nav['href']=$nav['menu_address'];
		}else{
			$nav['href']=UU('list/index',array('id'=>$nav['id']));
			if(strtolower($nav['menu_enname'])=='home' && $nav['parentid']==0){
				$nav['href']=UU('index/index');
			}
		}
		$navs[$key]=$nav;
	}
	F("site_nav_".$id,$navs);
	return $navs;
}

/**
 * 获取树形数组
 * @author rainfer <81818832@qq.com>
 *
 * @return array
 */
function get_menu_tree($id="main"){
	$navs=F("site_nav_".$id);
	if(empty($navs)){
		$navs=get_menu_datas($id);
	}
	import("Org.Util.Tree");
	$tree = new \Tree();
	$tree->init($navs);
	return $tree->get_tree_array(0, "");
}
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return ($suffix && $slice!=$str)? $slice.'...' : $slice;
}
/**
 * 查询文章列表，支持分页或不分页
 * @author rainfer <81818832@qq.com>
 *
 * @param string $type 查询类型,可以为'cid',可以为'keyword',可以为'tag'
 * @param string $v 当查询类型为'cid'或'keyword'时,待搜索的值
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:news_title,news_content;limit:0,8;order:news_time desc,news_hits desc;where:n_id>5;"<br>
 *  ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 	field:调用post指定字段,如(n_id,news_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始),使用分页时无效
 * 	order:排序方式，如：news_hits desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @param array $where 查询条件，（暂只支持数组），格式和thinkphp where方法一样；
 * @param bool $ispage 是否分页
 */
function get_news($tag,$ispage=false,$pagesize=10,$type=null,$v=null,$where=array()){
    $where=is_array($where)?$where:array();
    $tag=param2array($tag);
    $field = !empty($tag['field']) ? $tag['field'] : '*';
    $limit = !empty($tag['limit']) ? $tag['limit'] : '';
    $order = !empty($tag['order']) ? $tag['order'] : 'news_time';
    switch($type){
        case 'keyword':
			$where['news_title|news_key'] = array('like','%' . $v . '%');//关键字
			break;
		case 'tag':
			$where['news_tag'] = array('like','%,' . $v . ',%');//标签
			break;
        case 'cid':
            $cid=intval($v);
            $catids=get_menu_byid($cid,1);
            if(!empty($catids)){
                $catids=implode(",", $catids);
                //$catids="cid:$catids;";
            }else{
                $catids="";
            }
            $tag['cid']=$catids;//重新生成条件
			break;
        default:
    }
    //根据参数生成查询条件
    $where['news_open'] = array('eq',1);
    $where['news_back'] = array('eq',0);
    if (!empty($tag['cid'])) {
        $where['news_columnid'] = array('in',$tag['cid']);
    }
    if (!empty($tag['ids'])) {
        $where['n_id'] = array('in',$tag['ids']);
    }
    if (!empty($tag['where'])) {
        $where['_string'] = $tag['where'];
    }
    $join = "".C('DB_PREFIX').'admin as b on a.news_auto =b.admin_id';
    $rs= M("news");
    if($ispage){
        //使用分页
        $count=$rs->alias("a")->join($join)->field($field)->where($where)->count();
		$pagesize=$pagesize?$pagesize:C('DB_PAGENUM');
        $Page= new \Think\Page($count,$pagesize);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('theme',' %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
        $show= $Page->show();// 分页显示输出
        $content['page']=$show;
		$news=$rs->alias("a")->join($join)->field($field)->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
		$content['news']=$news;
		$content['count']=$count;
        return $content;
    }else{
        //不使用分页
        $news=$rs->alias("a")->join($join)->field($field)->where($where)->order($order)->limit($limit)->select();
        return $news;
    }
}
/**
 * 获取评论
 * @param string $tag
 * @param array $where //按照thinkphp where array格式
 */
function get_comments($tag="field:*;limit:0,5;order:createtime desc;",$where=array()){
    $where=is_array($where)?$where:array();
    $tag=param2array($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '5';
	$order = !empty($tag['order']) ? $tag['order'] : 'createtime desc';
	
	//根据参数生成查询条件
	$mwhere['c_status'] = array('eq',1);
	
	if(is_array($where)){
		$where=array_merge($mwhere,$where);
	}else{
		$where=$mwhere;
	}
	$join = "".C('DB_PREFIX').'member_list as b on a.uid =b.member_list_id';
	$comments_model=M("comments");
	$comments=$comments_model->alias("a")->join($join)->field($field)->where($where)->order($order)->limit($limit)->select();
	return $comments;
}
/**
 * 获取新闻分类ids
 * @author rainfer <81818832@qq.com>
 *
 * $id 待获取的id
 * $self 是否返回自身，默认false
 * @return array
 */
function get_menu_byid($id=0,$self=false){
    $arr=M('menu')->where(array('menu_open'=>1,'id'=>$id))->select();
    if($arr){
        $rst=$self?array($id):array();
        $menu=M('menu')->where(array('menu_open'=>1,'parentid'=>$id))->field('id')->select();
        foreach($menu as $v){
            $rst[]=intval($v['id']);
            $arr=M('menu')->where(array('menu_open'=>1,'parentid'=>$v['id']))->field('id')->select();
            if($arr){
                $rst=array_merge($rst,get_menu_byid($v['id'],false));
            }
        }
        return $rst;
    }else{
        return array();
    }
}
/**
 * 根据广告位获取所有广告
 * @author rainfer <81818832@qq.com>
 *
 * @param int $plug_ad_adtypeid 广告位id
 * @return array;
 */
function get_ads($plug_ad_adtypeid,$limit=5,$order = "plug_ad_order ASC"){
    $ad_obj= M("plug_ad");
    if($order == ''){
        $order = "plug_ad_order ASC";
    }
    if ($limit == 0) {
        $limit = 5;
    }
    return $ad_obj->where(array('plug_ad_open'=>1,'plug_ad_adtypeid'=>$plug_ad_adtypeid))->order($order)->limit('0,'.$limit)->select();
}
/**
 * 截取待html的文本
 * @author rainfer <81818832@qq.com>
 *
 * @param int $plug_ad_adtypeid 广告位id
 * @return array;
 */
function html_trim($html, $max, $suffix='...')
{
    $non_paired_tags = array('br', 'hr', 'img', 'input', 'param'); // 非成对标签
    $html = trim($html);
    $html = preg_replace('/<img([^>]+)>/i', '', $html);
    $count = 0; // 有效字符计数(一个HTML实体字符算一个有效字符)
    $tag_status = 0; // (0:非标签, 1:标签开始, 2:标签名开始, 3:标签名结束)
    $nodes = array(); // 存放解析出的节点(文本节点:array(0, '文本内容', 'text', 0), 标签节点:array(1, 'tag', 'tag_name', '标签性质:0:非成对标签,1:成对标签的开始标签,2:闭合标签'))
    $segment = ''; // 文本片段
    $tag_name = ''; // 标签名
    for($i=0;$i<strlen($html);$i++)
    {
        $char = $html[$i]; // 当前字符
        $segment .= $char; // 保存文本片段
        if($tag_status == 4)
        {
            $tag_status = 0;
        }
        if($tag_status == 0 && $char == '<')
        {
            // 没有开启标签状态,设置标签开启状态
            $tag_status = 1;
        }
        if($tag_status == 1 && $char != '<')
        {
            // 标签状态设置为开启后,用下一个字符来确定是一个标签的开始
            $tag_status = 2; //标签名开始
            $tag_name = ''; // 清空标签名
            // 确认标签开启,将标签之前保存的字符版本存为文本节点
            $nodes[] = array(0, substr($segment, 0, strlen($segment)-2), 'text', 0);
            $segment = '<'.$char; // 重置片段,以标签开头
        }
        if($tag_status == 2)
        {
            // 提取标签名
            if($char == ' ' || $char == '>' || $char == "\t")
            {
                $tag_status = 3; // 标签名结束
            }else
            {
                $tag_name .= $char; // 增加标签名字符
            }
        }
        if($tag_status == 3 && $char == '>')
        {
            $tag_status = 4; // 重置标签状态
            $tag_name = strtolower($tag_name);
            // 跳过成对标签的闭合标签
            $tag_type = 1;
            if(in_array($tag_name, $non_paired_tags))
            {
                // 非成对标签
                $tag_type = 0;
            }elseif($tag_name[0] == '/')
            {
                $tag_type = 2;
            }
            // 标签结束,保存标签节点
            $nodes[] = array(1, $segment, $tag_name, $tag_type);
            $segment = ''; // 清空片段
        }
        if($tag_status == 0)
        {
            //echo $char.')'.$count."\n";
            if($char == '&')
            {
                // 处理HTML实体,10个字符以内碰到';',则认为是一个HTML实体
                for($e=1;$e<=10;$e++)
                {
                    if($html[$i+$e] == ';')
                    {
                        $segment .= substr($html, $i+1, $e); // 保存实体
                        $i += $e; // 跳过实体字符所占长度
                        break;
                    }
                }
            }else
            {
                // 非标签情况下检查有效文本
                $char_code = ord($char); // 字符编码
                if($char_code >= 224) // 三字节字符
                {
                    $segment .= $html[$i+1].$html[$i+2]; // 保存字符
                    $i += 2; // 跳过下2个字符的长度
                }elseif($char_code >= 129) // 双字节字符
                {
                    $segment .= $html[$i+1];
                    $i += 1; // 跳过下一个字符的长度
                }
            }
            $count ++;
            if($count == $max)
            {
                $nodes[] = array(0, $segment.$suffix, 'text',0);
                break;
            }
        }
    }
    $html = '';
    $tag_open_stack = array(); // 成对标签的开始标签栈
    for($i=0;$i<count($nodes);$i++)
    {
        $node = $nodes[$i];
        if($node[3] == 1)
        {
            array_push($tag_open_stack, $node[2]); // 开始标签入栈
        }elseif($node[3] == 2)
        {
            array_pop($tag_open_stack); // 碰到一个结束标签,出栈一个开始标签
        }
        $html .= $node[1];
    }
    while($tag_name = array_pop($tag_open_stack)) // 用剩下的未出栈的开始标签补齐未闭合的成对标签
    {
        $html .= '</'.$tag_name.'>';
    }
    return $html;
}
/**
 * 获取单页面菜单
 * @author rainfer <81818832@qq.com>
 *
 * @param int $id 菜单id
 * @return array;
 */
function get_menu_one($id){
	$rst=array();
	if($id){
		$rst=M('menu')->where(array('menu_type'=>4,'id'=>$id))->find();
	}
    return $rst;
}
/**
 * 设置全局配置到文件
 *
 * @param $key
 * @param $value
 */
function sys_config_setbykey($key, $value)
{
    $file = './data/conf/config.php';
    $cfg = array();
    if (file_exists($file)) {
        $cfg = (include $file);
    }
    $item = explode('.', $key);
    switch (count($item)) {
        case 1:
            $cfg[$item[0]] = $value;
            break;
        case 2:
            $cfg[$item[0]][$item[1]] = $value;
            break;
    }
    file_put_contents($file, "<?php\nreturn " . var_export($cfg, true) . ";");
}
/**
 * 设置全局配置到文件
 *
 * @param array
 */
function sys_config_setbyarr($data)
{
    $file = './data/conf/config.php';
    if(file_exists($file)){
        $configs=include $file;
    }else {
        $configs=array();
    }
    $configs=array_merge($configs,$data);
    return file_put_contents($file, "<?php\treturn " . var_export($configs, true) . ";");
}
/**
 * 获取全局配置
 *
 * @param $key
 * @return null
 */
function sys_config_get($key)
{
    $file = './data/conf/config.php';
    $cfg = array();
    if (file_exists($file)) {
        $cfg = (include $file);
    }
    return isset($cfg[$key]) ? $cfg[$key] : null;
}
/**
 * 检查用户对某个url,内容的可访问性，用于记录如是否赞过，是否访问过等等;开发者可以自由控制，对于没有必要做的检查可以不做，以减少服务器压力
 * @param number $object 访问对象的id,格式：不带前缀的表名+id;如news1表示xx_news表里id为1的记录;如果object为空，表示只检查对某个url访问的合法性
 * @param number $count_limit 访问次数限制,如1，表示只能访问一次,0表示不限制
 * @param boolean $ip_limit ip限制,false为不限制，true为限制
 * @param number $expire 距离上次访问的最小时间单位s，0表示不限制，大于0表示最后访问$expire秒后才可以访问
 * @return true 可访问，false不可访问
 */
function check_user_action($object="",$count_limit=1,$ip_limit=false,$expire=0){
	$action_log_model=M("action_log");
	$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
	$userid=session('hid')?session('hid'):0;
	$ip=get_client_ip(0,true);
	$where=array("uid"=>$userid,"action"=>$action,"object"=>$object);
	if($ip_limit){
		$where['ip']=$ip;
	}
	$find_log=$action_log_model->where($where)->find();
	$time=time();
	if($find_log){
		//次数限制
		if($count_limit>0 && $find_log['count']>=$count_limit){
			return false;
		}
		//时间限制
		if($expire>0 && ($time-$find_log['last_time'])<$expire){
			return false;
		}
		$action_log_model->where($where)->save(array("count"=>array("exp","count+1"),"last_time"=>$time,"ip"=>$ip));
	}else{
		$action_log_model->add(array("uid"=>$userid,"action"=>$action,"object"=>$object,"count"=>array("exp","count+1"),"last_time"=>$time,"ip"=>$ip));
	}
	return true;
}
/**
 * 用于生成收藏内容用的key
 * @param string $table 收藏内容所在表
 * @param int $object_id 收藏内容的id
 */
function get_favorite_key($table,$object_id){
    $key=encrypt_password($table.'-'.$object_id,$table);
    return $key;
}
/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function UU($url='',$vars='',$suffix=true,$domain=false){
	$routes=get_routes();
	//dump($routes);
	if(empty($routes)){
		//不存在路由,则直接以U方法生成
		return U($url,$vars,$suffix,$domain);
	}else{
		// 解析URL
		$info=parse_url($url);
		//如果path为空,则path为方法,否则为path
		$url=!empty($info['path'])?$info['path']:ACTION_NAME;
		if(isset($info['fragment'])) { // 解析锚点
			$anchor=$info['fragment'];
			//瞄点含?,则第1部分为真瞄点,第2部分为参数查询部分
			if(false !== strpos($anchor,'?')) { // 解析参数
				list($anchor,$info['query'])=explode('?',$anchor,2);
			}
			//瞄点含@,则第1部分为真瞄点,第2部分为域名host
			if(false !== strpos($anchor,'@')) { // 解析域名
				list($anchor,$host)=explode('@',$anchor, 2);
			}
		}elseif(false !== strpos($url,'@')) { // 解析域名
			//path中含@,则第1部分为真正的path,赋值给url,第2为域名
			list($url,$host)=explode('@',$info['path'], 2);
		}


		// 解析参数
		if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();//既不是字符串,也不是数组,则为空数组
		}
		//合并参数
		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
			parse_str($info['query'],$params);
			$vars = array_merge($params,$vars);
		}
		$vars_src=$vars;
		ksort($vars);
		// URL组装
		$depr=C('URL_PATHINFO_DEPR');
		$urlCase=C('URL_CASE_INSENSITIVE');
		if('/' != $depr) { // 安全替换
			$url    =   str_replace('/',$depr,$url);
		}
		// 解析模块、控制器和操作
		$url        =   trim($url,$depr);
		$path       =   explode($depr,$url);
		$var        =   array();
		$varModule      =   C('VAR_MODULE');
		$varController  =   C('VAR_CONTROLLER');
		$varAction      =   C('VAR_ACTION');
		$var[$varAction]       =   !empty($path)?array_pop($path):ACTION_NAME;
		$var[$varController]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
		//处理方法映射
		if($maps = C('URL_ACTION_MAP')) {
			if(isset($maps[strtolower($var[$varController])])) {
				$maps    =   $maps[strtolower($var[$varController])];
				if($action = array_search(strtolower($var[$varAction]),$maps)){
					$var[$varAction] = $action;
				}
			}
		}
		//处理控制器映射
		if($maps = C('URL_CONTROLLER_MAP')) {
			if($controller = array_search(strtolower($var[$varController]),$maps)){
				$var[$varController] = $controller;
			}
		}
		if($urlCase) {
			$var[$varController]   =   parse_name($var[$varController]);
		}
		$module =   '';
		
		if(!empty($path)) {
			$var[$varModule]    =   array_pop($path);
		}else{
			if(C('MULTI_MODULE')) {
				//多模块
				if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
					$var[$varModule]=   MODULE_NAME;
				}
			}
		}
		//处理模块映射
		if($maps = C('URL_MODULE_MAP')) {
			if($_module = array_search(strtolower($var[$varModule]),$maps)){
				$var[$varModule] = $_module;
			}
		}
		if(isset($var[$varModule])){
			$module =   $var[$varModule];
		}
		//开始拼装
		if(C('URL_MODEL') == 0) { // 普通模式URL转换
			$url        =   __APP__.'?'.http_build_query(array_reverse($var));
			if($urlCase){
				$url    =   strtolower($url);
			}
			if(!empty($vars)) {
				$vars   =   http_build_query($vars);
				$url   .=   '&'.$vars;
			}
		}else{ // PATHINFO模式或者兼容URL模式
			if(empty($var[$varModule])){
				$var[$varModule]=MODULE_NAME;//模块为空,则以当前模块
			}
			$module_controller_action=strtolower(implode($depr,array_reverse($var)));//拼装成"模块/控制器/方法"
			//匹配路由规则
			$has_route=false;
			//拼装成原始url,形式"模块/控制器/方法?参数1=值1&参数2=值2"
			$original_url=$module_controller_action.(empty($vars)?"":"?").http_build_query($vars);
			if(isset($routes['static'][$original_url])){
				//存在静态路由
			    $has_route=true;
				//返回静态后的url
			    $url=__APP__."/".$routes['static'][$original_url];
			}else{
				//不存在静态路由,则开始查找动态路由
			    if(isset($routes['dynamic'][$module_controller_action])){
					//存在
			        $urlrules=$routes['dynamic'][$module_controller_action];//所有"模块/控制器/方法"的规则
			        $empty_query_urlrule=array();
			        foreach ($urlrules as $ur){
			            $intersect=array_intersect_assoc($ur['query'], $vars);//返回键名 键值都一样的
			            if($intersect){
			                $vars=array_diff_key($vars,$ur['query']);//所有$vars参数数组不在规则参数的数组
			                $url= $ur['url'];
			                $has_route=true;
			                break;//退出循环
			            }
						//不存在参数
			            if(empty($empty_query_urlrule) && empty($ur['query'])){
			                $empty_query_urlrule=$ur;
			            }
			        }
			        if(!empty($empty_query_urlrule)){
						//不含参数
			            $has_route=true;
			            $url=$empty_query_urlrule['url'];
			        }
			        $new_vars=array_reverse($vars);
			        foreach ($new_vars as $key =>$value){
			            if(strpos($url, ":$key")!==false){
			                $url=str_replace(":$key", $value, $url);
			                unset($vars[$key]);
			            }
			        }
			        $url=str_replace(array("\d","$"), "", $url);
			        if($has_route){
			            if(!empty($vars)) { // 添加参数
			                foreach ($vars as $var => $val){
			                    if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
			                }
			            }
			            $url =__APP__."/".$url ;
			        }
			    }//存在动态路由
			}
			$url=str_replace(array("^","$"), "", $url);
			//不存在路由
			if(!$has_route){
				$module =   defined('BIND_MODULE') ? '' : $module;
				$url    =   __APP__.'/'.implode($depr,array_reverse($var));
				if($urlCase){
					$url    =   strtolower($url);
				}
				if(!empty($vars)) { // 添加参数
					foreach ($vars as $var => $val){
						if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
					}
				}
			}
			//添加静态后缀
			if($suffix) {
				$suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
				if($pos = strpos($suffix, '|')){
					$suffix = substr($suffix, 0, $pos);
				}
				if($suffix && '/' != substr($url,-1)){
					$url  .=  '.'.ltrim($suffix,'.');
				}
			}
		}//pathinfo或兼容模式结束
		//添加瞄点
		if(isset($anchor)){
			$url  .= '#'.$anchor;
		}
		//添加域名
		if($domain) {
			$url   =  (is_ssl()?'https://':'http://').$domain.$url;
		}
		return $url;
	}
}
/**
 * 获取URL路由规则
 * @param boolean $refresh 是否刷新
 * @return array
 */
function get_routes($refresh=false){
	$routes=F("routes");
	if( (!empty($routes)||is_array($routes)) && !$refresh){
		return $routes;
	}
	$routes=M("route")->where("status=1")->order("listorder asc")->select();
	$all_routes_s=array();
	$all_routes_d=array();
	$cache_routes=array();
	foreach ($routes as $er){
		$full_url=htmlspecialchars_decode($er['full_url']);
		// 解析URL
		$info=parse_url($full_url);
		$path=explode("/",$info['path']);
		if(count($path)!=3){//必须是完整 url
			continue;
		}
		$module=strtolower($path[0]);
		// 解析参数
		$vars = array();
		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
			parse_str($info['query'],$params);
			$vars = array_merge($params,$vars);
		}
		$vars_src=$vars;
		ksort($vars);
		$path=$info['path'];
		$full_url=$path.(empty($vars)?"":"?").http_build_query($vars);
		//显示的url
		$url=$er['url'];
		if(strpos($url,':')===false){
			//静态,不含动态参数
		    $cache_routes['static'][$full_url]=$url;
			$all_routes_s[$url]=$full_url;
		}else{
			//动态
		    $cache_routes['dynamic'][$path][]=array("query"=>$vars,"url"=>$url);
			$all_routes_d[$url]=$full_url;
		}
	}
	F("routes",$cache_routes);
	$data = array('URL_MAP_RULES' => $all_routes_s);
	sys_config_setbyarr($data);
	$data = array('URL_ROUTE_RULES' => $all_routes_d);
	sys_config_setbyarr($data);
	return $cache_routes;
}