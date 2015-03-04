<?php
/**
 * 微信JS-SDK 类
 * @author fanrong33 <fanrong33@qq.com>
 * @version 1.0.1 build 20150303
 */
class WeixinJS {

    private $app_id;        // 应用ID
    private $app_secret;    // 应用密钥

    public function __construct($app_id, $app_secret) {
        $this->app_id     = $app_id;
        $this->app_secret = $app_secret;
    }

    /**
     * 获取签名包，提供前端使用wx.config({...})
     * @depend $jsapi_ticket
     * @return [type] [description]
     */
    public function getSignaturePackage() {
        $jsapi_ticket = $this->getJsApiTicket();
        $url          = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp    = time ();
        $nonce_str    = $this->generateNonceStr();
        
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$nonce_str&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        
        $signature_package = array (
            "app_id"     => $this->app_id,
            "nonce_str"  => $nonce_str,
            "timestamp"  => $timestamp,
            "url"        => $url,
            "signature"  => $signature,
            "raw_string" => $string
        );
        return $signature_package;
    }

    /**
     * js api票据
     * 依赖access_token
     * @return string $jsapi_ticket
     */
    public function getJsApiTicket() {
        $jsapi_ticket = $this->wxcache('jsapi_ticket');
        if (false === $jsapi_ticket) {
            // $access_token = $this->getAccessToken();
            $access_token = $this->_get_yunruan_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
            $params = array(
                    'type' => 'jsapi',
                    'access_token' => $access_token
            );
            $json = $this->api($url, $params);
            // {"errcode":0,"errmsg":"ok","ticket":"bxLdikRXVbTPdHSM05e5u_KIEEX654mXFxLPwaKONvQhKjNNYkJx3MIuv4vIi-NuLN27ArsTTbPSVreerFVQtg","expires_in":7200}
            $jsapi_ticket = $json['ticket'];
            if ($jsapi_ticket) {
                $this->wxcache('jsapi_ticket', $jsapi_ticket, $json['expires_in']);
            }
        }
        
        return $jsapi_ticket;
    }

    /**
     * 微信JS-SDK所有操作均依赖access_token
     */
    public function getAccessToken() {
        //access_token 应该全局存储与更新
        $access_token = $this->wxcache('access_token');
        if (false === $access_token) {

            $url = "https://api.weixin.qq.com/cgi-bin/token";
            $params = array(
                'grant_type' => 'client_credential',
                'appid'      => $this->app_id,
                'secret'     => $this->app_secret
            );
            $json = $this->api($url, $params);
            // {"access_token":"hQuCa3aFeYCmIdSM5gB3qIfYtb4NI3uHR5XX2FMEB07WK4A8p5xLIkGxUOgDzG6BjxXVTqwQLyFZAYpkHabRj0Z6w7V1IkJjIZctJfXCH6s","expires_in":7200}
            $access_token = $json['access_token'];
            if ($access_token) {
                $this->wxcache('access_token', $access_token, $json['expires_in']);
            }
        }
        return $access_token;
    }

    private function generateNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i ++) {
            $str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
        }
        return $str;
    }


    private function api($url, $params, $method='GET'){
        // $params['access_token']=$this->access_token;
        if($method == 'GET'){
            $result_str = self::http($url.'?'.http_build_query($params));
        }else{
            $result_str = self::http($url, http_build_query($params), 'POST');
        }
        dump($result_str);
        $result = array();
        if($result_str!='') $result = json_decode($result_str, true);
        return $result;
    }
    
    private function http($url, $postfields='', $method='GET', $headers=array()){
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER , false); 
        curl_setopt($ci, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT , 30);
        curl_setopt($ci, CURLOPT_TIMEOUT        , 30);
        if($method == 'POST'){
            curl_setopt($ci, CURLOPT_POST, true);
            if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        }
        // $headers[]="User-Agent: fanrong33.com";
        curl_setopt($ci, CURLOPT_HTTPHEADER     , $headers);
        curl_setopt($ci, CURLOPT_URL            , $url);
        $response = curl_exec($ci);
        curl_close($ci);
        return $response;
    }

    public function wxcache($key, $value='', $expire=7200){
        // 声明缓存文件保存地址
        $path = 'runtime/cache/';
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
        $file_prefix = md5($this->app_id.$this->app_secret).'_';

        $_cache = $this->F($file_prefix.'wxcache', '', $path);

        if($value !== ''){
            if(is_null($value)){
                // 删除缓存
                unset($_cache[$key]);
            }else{
                // 缓存数据
                $_cache[$key] = array(
                    'value'       => $value,
                    'expire_time' => time()+$expire
                );
            }
            return $this->F($file_prefix.'wxcache', $_cache, $path);
        }
        if(isset($_cache[$key])){
            // 缓存未过期
            if(time() <= $_cache[$key]['expire_time']){
                return $_cache[$key]['value'];
            }
        }
        return false;
    }

    // 快速文件数据读取和保存 针对简单类型数据 字符串、数组
    private function F($name, $value='', $path='') {
        static $_cache = array();
        $filename = $path . $name . '.php';
        if ('' !== $value) {
            if (is_null($value)) {
                // 删除缓存
                return unlink($filename);
            } else {
                // 缓存数据
                $dir = dirname($filename);
                // 目录不存在则创建
                if (!is_dir($dir))
                    mkdir($dir);
                return file_put_contents($filename, "<?php\nreturn " . var_export($value, true) . ";\n?>");
            }
        }
        if (isset($_cache[$name]))
            return $_cache[$name];
        // 获取缓存数据
        if (is_file($filename)) {
            $value = include $filename;
            $_cache[$name] = $value;
        } else {
            $value = false;
        }
        return $value;
    }

}