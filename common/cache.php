<?php
/*
define('APP_NAME', 'wechat');
$appid = 'wxxxxxxxxxxxxxx';
cache('wechat_js_ticket_cache_'.$appid, 'js_ticketxxxxxxx', 10);
echo cache('wechat_js_ticket_cache_'.$appid);
*/


/**
 * 简单文件缓存的设置和读取，主要用于配置等少量缓存
 *  - 支持配置缓存文件存储目录
 *  - 支持过期时间，access_token等会过期
 *  - 防止被下载，当然最好是将项目目录部署到htdoc/
 *  - 不能存储boolean的值
 * @param  string  $name   
 * @param  string  $value
 * @param  integer $expire_in 缓存秒数，如果为0则长期缓存
 * @param  string  $path
 * @return boolean 
 */
function cache($name, $value='', $expire_in='', $path='') {
    static $_cache = array();
    $file = $path . $name . '.php';

    //取得缓存对象实例
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            // $result = $cache->rm($name);
            if(is_file($file)){
                @unlink($file);
            }
            unset($_cache[$name]);
            return true;
        }else {
            // 缓存数据
            // $cache->set($name, $value, $expire);
            $dir = dirname($file);
            if(!is_dir($dir)){
                mkdir($dir, true);
            }
            $data = array(
                'value'       => $value,
                'expire_time' => $expire_in ? (time()+$expire_in) : 0,
            );
            // file_put_contents($file, serialize($data));
            //created time：2015-03-04 16:57:01
            $content = "<?php\n".
                       "// created time: ".date('Y-m-d H:i:s')."\n".
                       'defined("APP_NAME") or exit(\'No permission resources.\');'."\n".
                       "return ". preg_replace('/\s+/', ' ', str_replace("\n", '', var_export($data, true))) . ";\n?>"; // 去掉换行
            file_put_contents($file, $content);
            $_cache[$name] = $value;
        }
        return;
    }
    if (isset($_cache[$name])){
        return $_cache[$name];
    }

    // 获取缓存数据
    // $value = $cache->get($name);
    if(!is_file($file)){
        return false;
    }

    $data = require $file;
    if (!is_array($data) || !isset($data['value']) || (!empty($data['value']) && $data['expire_time']<time())) {
        @unlink($file);
        return false;
    }
    $_cache[$name] = $data['value'];
    return $data['value'];
}

?>