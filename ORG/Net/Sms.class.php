<?php
/**
 * 短信接口工具类（for smsbao.cn）
 * @author fanrong33
 * @version v1.0.2 Build 20150318
 */
class Sms{
	
	//TODO 对不同类型的业务短信做安全验证，防止短信轰炸机
	//网站手机验证、APP应用手机验证、订单通知、物流提醒
	
	/**
	 * 发送短信
	 * 
	 * @param string 	$phone		
	 * @param string	$content
	 * 
	 * @return bollean
	 */
	public static function sendSms($mobile, $content){
		$smsapi   = "http://www.smsbao.com/sms"; // 短信网关
		$username = "test"; 	// 短信平台帐号
		$password = "111111";   	// 短信平台密码 
		
		// 如果不是UTF-8编码，则进行urlencode
		if(!self::is_utf8($content)){
		    $content = urlencode($content);
		}

		$params = array(
			'u' => $username,
			'p' => md5($password),
			'm' => $mobile,
			'c' => $content,
		);
		
		$code = self::api($smsapi, $params, 'GET');
		if($code == 0){
			$result = array(
				'data'   => '',
				'status' => 1,
				'info'   => '短信发送成功',
			);
			return $result;
		}else{
			$map = array(
				30 => '密码错误',
				40 => '账号不存在',
				41 => '余额不足',
				42 => '账号过期',
				43 => 'IP地方限制',
				50 => '内容含有敏感词',
				51 => '手机号码不正确',
			);
			$result = array(
				'data'   => '',
				'status' => 0,
				'info'   => $map[$code],
				'code'   => $code,
			);
			return $result;
		}
	}
	
	static function api($url, $params, $method='GET'){
		// $params['access_token']=$this->access_token;
		if($method == 'GET'){
			$result_str = self::http($url.'?'.http_build_query($params));
		}else{
			$result_str = self::http($url, http_build_query($params), 'POST');
		}
		
		return $result_str;
	}
	
	static function http($url, $postfields='', $method='GET', $headers=array()){
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER , false); 
		curl_setopt($ci, CURLOPT_RETURNTRANSFER , true);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT , 30);
		curl_setopt($ci, CURLOPT_TIMEOUT		, 30);
		if($method == 'POST'){
			curl_setopt($ci, CURLOPT_POST, true);
			if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
		}
//		$headers[]="User-Agent: fanrong33.com";
		curl_setopt($ci, CURLOPT_HTTPHEADER		, $headers);
		curl_setopt($ci, CURLOPT_URL			, $url);
		$response = curl_exec($ci);
		curl_close($ci);
		return $response;
	}
	
	/**
	 * 判断字符编码是否为UTF8
	 */
	static function is_utf8($content){
        if(preg_match("/^([" .chr(228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){1}/" ,$content ) == true || preg_match("/([" .chr (228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){1} $/",$content) == true || preg_match("/([" .chr (228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){2,}/" , $content) == true){
            return true;
        } else{
            return false;
        }
	}
	
}
?>
