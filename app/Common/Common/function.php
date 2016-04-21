<?php
use Think\Db;
use Think\Storage;
function p ($array){
    dump($array,1,'<pre>',0);
}

function get_avatar_name(){
    return 'temp';
}

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

/**************************数据备份操作*****************************/

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