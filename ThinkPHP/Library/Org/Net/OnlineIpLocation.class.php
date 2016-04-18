<?php
// +----------------------------------------------------------------------
// | Author: qufo <qufo@163.com>
// +----------------------------------------------------------------------
namespace Org\Net;
/**
 *  IP 鍦扮悊浣嶇疆鏌ヨ绫� 浠庡湪绾跨殑API涓煡璇�
 * @author    qufo (qufo@163.com)
 */
class OnlineIpLocation {
    public  $confname;
    private $sina_url    = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=';

    public function getConfList(){
       return array(
         'sina'     => $this->sina_url,

       ); 
    }
    
    public function getlocation($ip='') {
        if(empty($ip)) $ip = get_client_ip();
        $str = '';
        $result = array();
        
        
      
                $url = $this->sina_url.$ip;
                $str = @file_get_contents($url);
                if ($str) {
                    $IpO = json_decode($str);
                    //var_dump($IpO);die;
                    if ($IpO->ret) {
                        $result['province'] = $IpO->province;
                        $result['city']     = $IpO->city;
                        $result['country']   = $IpO->country;
                        $result['ip']       = $ip;
                        $result['isp']      = $IpO->isp;
                    }
                }

        return $result;
    }
}
