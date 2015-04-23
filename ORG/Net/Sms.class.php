<?php
/**
 * 短信接口工具类（for smsbao.cn）
 * @author fanrong33
 * @version v1.0.3 Build 20150423
 */
class Sms{
	
	//TODO 对不同类型的业务短信做安全验证，防止短信轰炸机，简单SESSION验证
	//网站手机验证、APP应用手机验证、订单通知、物流提醒
	
	private $_smsapi_url = 'http://www.smsbao.com/sms'; // 短信网关
	private $_username; // 短信平台帐号
	private $_password; // 短信平台密码 

	public function __construct($username, $password){
		$this->_username = $username;
		$this->_password = $password;
	}

	/**
	 * 发送短信
	 * 
	 * @param string 	$phone		
	 * @param string	$content
	 * 
	 * @return array
	 * array(
	 * 	   'data'   => '',
	 * 	   'info'	=> '短信发送成功',
	 * 	   'status' => 1,
	 * 	   'code'	=> 30, // status=0 时存在
	 * )
	 */
	public function sendSms($mobile, $content){
		
		// 如果不是UTF-8编码，则进行urlencode
		if(!self::is_utf8($content)){
		    $content = urlencode($content);
		}

		$params = array(
			'u' => $this->_username,
			'p' => md5($this->_password),
			'm' => $mobile,
			'c' => $content,
		);
		
		$code = self::api($this->_smsapi_url, $params, 'GET');
		if($code == 0){
			$result = array(
				'data'   => '',
				'status' => 1,
				'info'   => '短信发送成功',
			);
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
		}
		return $result;
	}
	
	private function api($url, $params, $method='GET'){
		// $params['access_token']=$this->access_token;
		if($method == 'GET'){
			$result_str = self::http($url.'?'.http_build_query($params));
		}else{
			$result_str = self::http($url, http_build_query($params), 'POST');
		}
		
		return $result_str;
	}
	
	private function http($url, $postfields='', $method='GET', $headers=array()){
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
	private function is_utf8($content){
        if(preg_match("/^([" .chr(228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){1}/" ,$content ) == true || preg_match("/([" .chr (228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){1} $/",$content) == true || preg_match("/([" .chr (228)."-". chr(233)."]{1}[" .chr (128)."-". chr(191)."]{1}[" .chr (128)."-". chr(191)."]{1}){2,}/" , $content) == true){
            return true;
        } else{
            return false;
        }
	}
	
}
?>
