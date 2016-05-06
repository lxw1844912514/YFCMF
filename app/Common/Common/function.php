<?php
use Think\Db;
use Think\Storage;

function sendMail($to, $title, $content) {

    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,"尊敬的客户");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = ""; //邮件正文不支持HTML的备用显示
    return($mail->Send());
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
 * @author slackck <876902658@qq.com>
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
        $table=str_replace(C('DB_PREFIX'),"",$table);
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
                    $vv = "'" . mysql_real_escape_string($vv) . "'";
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
 * @param $table : 不含前缀的数据表名
 * @return bool
 */
function db_is_valid_table_name($table)
{
    return in_array($table, db_get_tables());
}
/**
 * 不检测表前缀,恢复数据库
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
 * @return string
 */
function db_get_db_prefix_holder()
{
    return '<--db-prefix-->';
}
/**
 * 强制下载
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
 *
 * @param string $dir
 * @param string $ext
 * @param string $content
 * @return string
 */
function save_storage_content($ext = null, $content = null, $filename = '')
{
    $newfile = '';
	//$path=C("TMPL_PARSE_STRING.__UPLOAD__");
	//$path=substr($path,0,1)=='/' ? substr($path,1) :$path;
    if ($ext && $content) {
        do {
            $newfile = 'data/upload/' . date('Y-m-d/') . uniqid() . '.' . $ext;
        } while (Storage::has($newfile));
        Storage::put($newfile, $content);
    }
    return $newfile;
}
/**
 * 返回带协议的域名
 */
function get_host(){
	$host=$_SERVER["HTTP_HOST"];
	$protocol=is_ssl()?"https://":"http://";
	return $protocol.$host;
}
/**
 * 获取后台管理设置的网站信息，此类信息一般用于前台
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
 * 获取所有友情连接
 * @return array
 */
function get_links($type=1){
	$links_obj= M("plug_link");
	return $links_obj->where(array('plug_link_typeid'=>$type,'plug_link_open'=>1))->order("plug_link_order ASC")->select();
}
/**
 * 返回指定id的菜单
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
 sp_get_menu($id,$effected_id,$filetpl,$foldertpl,$ul_class,$li_class,$style,$showlevel);
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
			$nav['href']=U('list/index',array('id'=>$nav['id']));
			if(strtolower($nav['menu_enname'])=='home' && $nav['parentid']==0){
				$nav['href']=U('index/index');
			}
		}
		$navs[$key]=$nav;
	}
	F("site_nav_".$id,$navs);
	return $navs;
}

/**
 * 获取树形数组
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
 * Author: Raifner <81818832@qq.com> 2016-2-22
 * @param string $type 查询类型,可以为'cid',可以为'keyword',可以为'tag'
 * @param string $v 当查询类型为'cid'或'keyword'时,待搜索的值
 * @param string $tag  查询标签，以字符串方式传入,例："cid:1,2;field:post_title,post_content;limit:0,8;order:post_date desc,listorder desc;where:id>0;"<br>
 *  ids:调用指定id的一个或多个数据,如 1,2,3<br>
 * 	cid:数据所在分类,可调出一个或多个分类数据,如 1,2,3 默认值为全部,在当前分类为:'.$cid.'<br>
 * 	field:调用post指定字段,如(id,post_title...) 默认全部<br>
 * 	limit:数据条数,默认值为10,可以指定从第几条开始,如3,8(表示共调用8条,从第3条开始),使用分页时无效
 * 	order:排序方式，如：post_date desc<br>
 *	where:查询条件，字符串形式，和sql语句一样
 * @param array $where 查询条件，（暂只支持数组），格式和thinkphp where方法一样；
 * @param bool $ispage 是否分页
 * @param int $pagesize 每页条数.
 * @param array $pagesetting 分页设置<br>
 * 	参数形式：<br>
 * 	array(<br>
 * 		&nbsp;&nbsp;"listlong" => "9",<br>
 * 		&nbsp;&nbsp;"first" => "首页",<br>
 * 		&nbsp;&nbsp;"last" => "尾页",<br>
 * 		&nbsp;&nbsp;"prev" => "上一页",<br>
 * 		&nbsp;&nbsp;"next" => "下一页",<br>
 * 		&nbsp;&nbsp;"list" => "*",<br>
 * 		&nbsp;&nbsp;"disabledclass" => ""<br>
 * 	)
 * @param string $pagetpl 以字符串方式传入,例："{first}{prev}{liststart}{list}{listend}{next}{last}"
* @return array 包括分页的文章列表<br>
 * array(<br>
 * 	&nbsp;&nbsp;"posts"=>"",//文章列表，array<br>
 * 	&nbsp;&nbsp;"page"=>""//分页html<br>
* )
 */
function get_post($type=null,$v=null,$tag,$where=array(),$ispage,$pagesize=20,$pagesetting=array(),$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}',$Current_page=1){
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
			$where['news_tag'] = array('IN','%' . $v . '%');//标签
			break;
        case 'cid':
            $cid=intval($v);
            $catids=array();
            $terms=M("Terms")->field("term_id")->where("status=1 and ( term_id=$cid OR path like '%-$cid-%' )")->order('term_id asc')->select();
            foreach($terms as $item){
                $catids[]=$item['term_id'];
            }
            if(!empty($catids)){
                $catids=implode(",", $catids);
                $catids="cid:$catids;";
            }else{
                $catids="";
            }
            $tag['cid']=$catids;//重新生成条件
			break;
        default:
    }
    //根据参数生成查询条件
    $where['status'] = array('eq',1);
    $where['post_status'] = array('eq',1);
    if (!empty($tag['cid'])) {
        $where['term_id'] = array('in',$tag['cid']);
    }
    if (!empty($tag['ids'])) {
        $where['object_id'] = array('in',$tag['ids']);
    }
    if (!empty($tag['where'])) {
        $where['_string'] = $tag['where'];
    }
    $join = "".C('DB_PREFIX').'posts as b on a.object_id =b.id';
    $join2= "".C('DB_PREFIX').'users as c on b.post_author = c.id';
    $rs= M("TermRelationships");
    if($ispage){
        //使用分页
		//需要处理重复字段(排除c表id字段)				//$field=($field=='*')?'a.*,b.*,user_login,user_pass,user_nicename,user_email,user_url,avatar,sex,birthday,signature,last_login_ip,last_login_time,create_time,user_activation_key,user_status,score,user_type,coin,mobile':$field;
		//$sub=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->limit($limit)->buildsql();
		//$totalsize=M()->table($sub.' d')->count();
        $totalsize=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->count();
		import('Page');
		if ($pagesize == 0) {
			$pagesize = 20;
		}
		$PageParam = C("VAR_PAGE");
		$page = new \Page($totalsize,$pagesize,$Current_page);
		$page->setLinkWraper("li");
		$page->__set("PageParam", $PageParam);
		$page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
		//$sub=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($limit)->buildsql();
		//$posts=M()->table($sub.' d')->limit($page->firstRow . ',' . $page->listRows)->select();
		$posts=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
		$content['posts']=$posts;
		$content['page']=$page->show('default');
		$content['count']=$totalsize;
		$content['pagesize']=$pagesize;
		$next_page=($page->Current_page<$page->Total_Pages)?$page->Current_page+1:0;
		$content['next_page']=$next_page;
        return $content;
    }else{
        //不使用分页
        $posts=$rs->alias("a")->join($join)->join($join2)->field($field)->where($where)->order($order)->limit($limit)->select();
        return $posts;
    }
}