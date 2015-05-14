<?php
/**
 +----------------------------------------------------------------------------
 * 邮件发送类
 +----------------------------------------------------------------------------
 * @author fanrong33
 * @version v1.0.0 Build 20111226
 +------------------------------------------------------------------------------
 */
 class Mail{
 	private $_address = ''; // 收件人地址
 	private $_vars = array();
 	private $_tpl_path = 'Public/mailtpl/'; // 邮件模板存放
	
	/**
	 * 构造函数
	 */
	function __construct($address){
		if (empty($address)) throw_exception("未设置收件人地址");
		$this->_address		= $address; 
	} 

	/**
	 * assign($name, $value)
	 * 
	 * 向E-mail模板赋值
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function assign($name, $value){
		if (empty($name)) return ;
		if (is_array($name)){
			$value = is_array($value) ? $value : array($value);
			foreach($name as $k => $v){
				$v = trim($v);
				if (empty($v)) continue;
				$this->_vars[$v] = $value[$k];
			}
		}else{
			$this->_vars[$name] = $value;
		}
		return $this;
	}
	
	/**
	 * 发送邮件
	 * 
	 * @param string $tplname 模板名称，如register
	 * @param string $subject 主题
	 * @param boolean $html 邮件内容是否为HTML
	 */
	public function send($address, $tplname, $subject, $html = true){
		try{
			vendor('PHPMailer.class#phpmailer');
			
			$Mail = new PHPMailer();
			
			$Mail->Subject = $subject;
			$body = $this->_display($tplname);
			
			if($html){
				$Mail->IsHTML(true);	// 内容为HTML格式
				$Mail->AltBody = 'text/html';
				$Mail->CharSet = 'UTF-8';
			}
			$Mail->MsgHTML($body);
			$mailtype = 'smtp';
			if($mailtype == 'smtp'){
				$Mail->IsSMTP();
				$Mail->SMTPDebug	= false;
				$Mail->SMTPAuth 	= true;
				// 从配置文件获取
				$Mail->Host			= C('MAIL_HOST'); // "smtp.qq.com";
				$Mail->Port			= C('MAIL_PORT'); // 25;
				$Mail->Username		= C('MAIL_USERNAME'); // "fanrong33@qq.com";
				$Mail->Password		= C('MAIL_PASSWORD'); // "...";
			}else{
				$Mail->IsMail();
			}
			
			$Mail->SetFrom(C('MAIL_USERNAME'), C('MAIL_FROM_NAME')); // 设置发件人邮箱地址和名称
//			$Mail->AddReplyTo("fanrong33@qq.com", "fanrong33");
			
			$Mail->AddAddress($address, substr($address, 0, strpos($address, '@'))); // 设置收件人地址和名称
			
			return $Mail->Send();
		}catch(phpmailerException $e){
			throw_exception($e->ErrorInfo);
		}
	}
	
	private function _display($tplname){
		if(empty($tplname)) throw_exception("未指定模板文件");
		$tpl = APP_PATH . '/' . $this->_tpl_path . $tplname . '.html';
	
		if (!file_exists($tpl)) throw_exception("模板文件{$tplname}不存在");
		
		$content = file_get_contents($tpl);
		if($this->_vars){
			foreach($this->_vars as $name => $value){
				$search[] 	= '{$' . $name . '}';
				$replace[]	= $value; 
			}
			$content = str_replace($search, $replace, $content);
		}
		$content = preg_replace('/\{\$.+?\}/', '', $content);		
		return $content;
	}
	
 }
 
?>