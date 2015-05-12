<?php
// +======================================================================
// | SUNCCO [ MIND -> SPEAK -> ACTION ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.suncco.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 蔡繁荣 <fanrong33@139.com> <QQ:353779190>
// +----------------------------------------------------------------------
// | Created on 2010-11-25
// +======================================================================

/**
 +------------------------------------------------------------------------------
 * 图像操作类库
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author   fanrong33 <fanrong33@qq.com> <QQ:353779190>
 * @version   0.9 build 2010-11-25
 +------------------------------------------------------------------------------
 */
class ImageUtil {
	
	/**
	 +----------------------------------------------------------
	 * 限制最大宽高进行缩放图像
	 *
	 +----------------------------------------------------------
	 * @static
	 * @access public
	 +----------------------------------------------------------
	 * @param string $img 图像资源
	 * @param string $maxWidth 最大宽度
	 * @param string $maxHeight 最大高度
	 +----------------------------------------------------------
	 * @return mixed
	 +----------------------------------------------------------
	 */
	public static function resize($img, $maxWidth, $maxHight){
		$width = imagesx($img);
		$height = imagesy($img);
		if($width >= $height){
			$zoomWidth = $maxWidth;
			$ratio = $maxWidth / $width;
			$zoomHeight = abs((int)Math.round($ratio * $height));
		}else{
			$zoomHeight = $maxHight;
			$ratio = $maxHight / $height;
			$zoomWidth = abs((int)Math.round($ratio * $width));
		}
		
		$zoomImg = imagecreatetruecolor($zoomWidth, $zoomHeight);
		imagecopyresampled($zoomImg, $img, 0, 0, 0, 0, $zoomWidth, $zoomHeight, $width, $height);
		return $zoomImg;
	}
	
	/**
	 +----------------------------------------------------------
	 * 裁剪图片
	 * @param resource 	$srcImg 	图像资源
	 * @param array     $coords 	裁剪的起始坐标和宽度高度
	 * @param integer 	$cropWidth 	裁剪后的宽度
	 * @param integer	$cropHeight 裁剪后的高度
	 +----------------------------------------------------------
	 * @return resource $cropImg	裁剪后的图像资源
	 +----------------------------------------------------------
	 */
	public static function crop($srcImg, $coords, $cropWidth, $cropHeight){
		
		$cropImg = imagecreatetruecolor( $cropWidth, $cropHeight);
		
		//计算比例
		$rx = $cropWidth / $coords['w'];
		$ry = $cropHeight / $coords['h'];
		
		//取得缩放后的宽和高
		$zoomWidth = abs((int)Math.round($rx * imagesx($srcImg)));
		$zoomHeight = abs((int)Math.round($ry * imagesy($srcImg)));
			
		//取得缩放的图片
		$zoomImg = imagecreatetruecolor($zoomWidth, $zoomHeight);
		imagecopyresampled($zoomImg,$srcImg,0,0,0,0,$zoomWidth, $zoomHeight, imagesx($srcImg), imagesy($srcImg));
			
		//取得缩放图片的相对位置
		$cropX = abs((int)Math.round($rx * $coords['x']));
		$cropY = abs((int)Math.round($ry * $coords['y']));
			
		imagecopyresampled($cropImg, $zoomImg, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		return $cropImg;
	}
	
    public static function output($im,$type='png',$filename='',$quality='100')
    {
        $ImageFun='image'.$type;
		if(empty($filename)) {
	        header("Content-type: image/".$type);
	        $ImageFun($im);
		}else{
	        $ImageFun($im,$filename, $quality);
		}
        imagedestroy($im);
    }
    	
}

?>