<?php
/**
 * 日志处理类
 *  - 原生的日志类，不依赖任何第三方框架
 *  - 日志超出大小自动进行分割
 *  
 * 日志记录的作用和方法：http://fanrong33.com/archives/173.html
 * @author fanrong33 <fanrong33@qq.com>
 * @version 1.0.0 build 20150412
 */
/* eg:
    $t1 = microtime(true);
    // business logical
    $t2 = microtime(true);
    Log::info("execution cost : " + round($t2-$t1, 3) + "s"); 
*/
class Log {

    // 日志级别 从上到下，由低到高
    const ERROR     = 'ERROR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';   // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';   // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';    // SQL：SQL语句 注意只在调试模式开启时有效

    // 日期格式
    static $format  =  '[ c ]';

    // 日志文件大小
    static $log_file_size = 536870912; // 512M 1024*1024*512 = 536870912

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message       日志信息
     * @param string $level         日志级别
     * @param string $destination   写入目标 date('y_m_d').'.log'
     * @return boolean
     */
    static function write($message, $level=self::ERR, $destination='') {
        $now = date(self::$format);
        if(is_array($message)){
            $message = print_r($message, true);
        }
        if(empty($destination)){ 
            $destination = date('y_m_d').'.log';
        }
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor(self::$log_file_size) <= filesize($destination) ){
            rename($destination, dirname($destination).'/'.time().'-'.basename($destination));
        }
        return file_put_contents($destination, "{$now} {$level}: {$message}\r\n", FILE_APPEND) > 0 ? true : false;
    }

    /**
     * 1. 对外部的调用封装，方便接口调试
     * 2. 记录重要方法或模块的输入与输出，方便定位
     * 3. 大批量数据的执行进度
     */
    static function debug($message, $destination=''){
        return self::write($message, self::DEBUG, $destination);
    }

    /**
     * 程序在运行就像一个机器人，我们可以从它的日志看出它正在做什么，是不是按预期的设计在做
     * 1. 状态变化
     * 2. 程序运行时间
     * 3. 关键变量及正在做哪些重要的事情
     */
    static function info($message, $destination=''){
        return self::write($message, self::INFO, $destination);
    }

    /**
     * 1. 业务异常
     * 
     */
    static function warn($message, $destination=''){
        return self::write($message, self::WARN, $destination);
    }

    /**
     * 1. 业务异常
     */
    static function error($message, $destination=''){
        return self::write($message, self::ERROR, $destination);
    }

}

?>