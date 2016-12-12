<?php
// +----------------------------------------------------------------------
// | LTHINK [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://LTHINK.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 涛哥 <liangtao.gz@foxmail.com>
// +----------------------------------------------------------------------
// | WeixinSDK.class.php 2015-12-02
// +----------------------------------------------------------------------
use LT\ThinkSDK\ThinkOauth;

class AlipaySDK extends ThinkOauth
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $GetRequestCodeURL = 'https://mapi.alipay.com/gateway.do';

    /**
     * API根路径
     * @var string
     */
    protected $ApiBase = 'https://mapi.alipay.com/gateway.do';

    public function getRequestCodeURL()
    {
        $params = array(
            'input_charset' => 'utf-8',
            'login_service' => 'user_auth',
            'partner' => $this->AppKey,
            'request_id' => date('YmdHis') . rand(1000, 9999),
            'return_url' => $this->Callback,
            'return_url_failed' => $this->Callback,
            'service' => 'wap.user.common.login',
        );
        $params['sign'] = $this->createSign($params);
        $params['sign_type'] = 'MD5';
        return $this->GetRequestCodeURL . '?' . http_build_query($params);
    }

    public function getAccessToken($code = '', $extend = '')
    {
        $sign = $_GET['sign'];
        unset($_GET['a'], $_GET['type'], $_GET['sign']);
        if ($sign === $this->createSign($_GET)) {
            $this->Token = array(
                'access_token' => $_GET['login_token'],
                'openid' => $_GET['user_id'],
            );
            return $this->Token;
        } else {
            exit('签名验证失败');
        }
    }

    protected function createSign($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }
        unset ($k, $v);
        return md5($stringToBeSigned . $this->AppSecret);
    }

    /**
     * 组装接口调用参数 并调用接口
     */
    public function call($api, $param = array(), $method = 'GET', $multi = false)
    {
        $param['partner'] = $this->AppKey;
        $param['sign'] = $this->createSign($param);
        $param['sign_type'] = 'MD5';
        $data = $this->http($this->url($api), $param, $method);
        return ($data);
    }

    /**
     * 解析access_token方法请求后的返回值
     */
    protected function parseToken($result, $extend)
    {

    }

    /**
     * 获取当前授权应用的openid
     */
    public function openid()
    {
        $data = $this->Token;
        if (!empty($data['openid']))
            return $data['openid'];
        else
            exit('没有获取到支付宝用户ID！');
    }

}

?>

