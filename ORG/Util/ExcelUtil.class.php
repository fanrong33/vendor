<?php
/**
 * 基于PHPExcel的Excel处理 工具类
 * 特性：
 *  - 导入:
 *  - 支持通过表头配置导入excel文件得到格式化数组
 *  - 导出:
 *  - 支持通过表头导出格式化内容的excel文件
 *  - 支持通过表头过滤筛选是否显示单元格列
 * 
 * @author fanrong33
 * @version v1.1.2 Build 20150428
 */
class ExcelUtil{
	
	/**
	 * 导入xls文件，将Excel文件转换成数组，适配器模式包装，支持表头配置文件组装row
	 * 依赖 import()方法
	 *  $header_map = array(
     *      'id'          => 'ID',
     *      'name'        => '姓名',
     *      'age'         => '年龄',
     *      'percent'     => array('title'=>'坚持率',  'sprintf' => "%d%%"),
     *      'wage'        => array('title'=>'工资',    'number_format' => 2), // 2 or 0 默认为., number_format($number, 2, '.', '')
     *      'status'      => array('title'=>'状态',    'enum' => array(1=>'正常', 2=>'锁定')),
     *      'create_time' => array('title'=>'创建时间', 'date'=>'Y-m-d H:i:s'),
     * );
	 * 
	 * @param array  	$header_map  导出文件Excel标题HashMap，对应字段名=>别名，支持格式化!
	 * @param string	$excel_file  xls文件
	 * @return array    $list  		 导出的原始数据列表
	 */
	public static function importDecorator($header_map, $excel_file){

        $sheet = self::import($excel_file, true); // 去掉标题

		// 过滤重组得到 $header_map
        $tmp_header_map = array();
        foreach ($header_map as $field => $format) {
            if(is_array($format)){
                $tmp_header_map[$field] = $format['title'];
            }else{
                $tmp_header_map[$field] = $format;
            }
        }

        // 为list组装key
        $tmp_list = array();
        foreach ($sheet as $row) {
        	if($row){
	            $row = array_combine(array_keys($tmp_header_map), $row);
	            $tmp_list[] = $row;
        	}
        }
        $list = $tmp_list;
        unset($tmp_header_map, $tmp_list);

		return $list;
	}


	/**
	 * 导入xls文件，将Excel文件转换成数组，原始基础方法
	 * 
	 * @param string	$excel_file  	xls文件
	 * @param boolean	$remove_title	是否去掉标题
	 * @return array $sheet
	 */
	public static function import($excel_file, $remove_title=true){
		
		//载入PHPExcel入口文件
		vendor('PHPExcel.PHPExcel', '', '.class.php');
		
		$reader = new PHPExcel_Reader_Excel2007();
		if(!$reader->canRead($excel_file)){
			$reader = new PHPExcel_Reader_Excel5();
			if(!$reader->canRead($excel_file))
				exit('对不起，该Excel文件无法读取！');
		}
		$excel = $reader->load($excel_file);
		$sheet = $excel->getSheet()->toArray();
		
		if($remove_title){
			array_shift($sheet); // 去掉标题说明
		}
		
		return $sheet;
	}
	
	/**
	 * 导出xls文件，将数组转换成Excel文件，适配器模式包装，支持表头格式化内容
	 * 依赖 export()方法
	 *  $header_map = array(
     *      'id'          => 'ID',
     *      'name'        => '姓名',
     *      'age'         => '年龄',
     *      'percent'     => array('title'=>'坚持率',  'sprintf' => "%d%%"),
     *      'wage'        => array('title'=>'工资',    'number_format' => 2), // 2 or 0 默认为., number_format($number, 2, '.', '')
     *      'status'      => array('title'=>'状态',    'enum' => array(1=>'正常', 2=>'锁定')),
     *      'create_time' => array('title'=>'创建时间', 'date'=>'Y-m-d H:i:s'),
     * );
	 * 
	 * @param array  	$header_map 导出文件Excel标题HashMap，对应字段名=>别名，支持格式化!
	 * @param array 	$list       待导出的原始数据列表
	 * @param string	$filename	导出的文件名，当$download=false时，$filename需要具体到保存的路径
	 * @param boolean   $download   是否导出为下载，默认是
	 * @return 下载文件 或者 excel文件名
	 */
	public static function exportDecorator($header_map, $list, $filename, $download=true){
		// 根据格式重构 $list
        $tmp_list       = array();
        foreach($list as $key => $rs){
            // $rs = array('id'=>1, 'name'=>'蔡繁荣', 'age'=>25, 'create_time'=>1430209609),
            // 
            $tmp_data = array();
            foreach($header_map as $field => $format){
                if(is_array($format)){
                    // $format  =  array('title'=>'创建时间', 'date'=>'Y-m-d')
                    foreach($format as $format_key => $format_val){
                        switch ($format_key) {
                            case 'date': // date('Y-m-d', $rs[$field])
                                $tmp_data[$field] = $format_key($format_val, $rs[$field]);
                                break;
                            case 'sprintf': // sprintf('%d', $rs[$field])
                                $tmp_data[$field] = $format_key($format_val, $rs[$field]);
                                break;
                            case 'number_format': // number_format($number, 2, '.', '')
                                $tmp_data[$field] = $format_key($rs[$field], $format_val, '.', '');
                                break;
                            case 'enum':
                                $tmp_data[$field] = $format_val[$rs[$field]];
                                break;
                            default:
                                break;
                        }
                    }
                }else{
                    $tmp_data[$field] = $rs[$field];
                }
            }
            $tmp_list[] = $tmp_data;
        }

        // 过滤重组得到 $header_map
        // dump($tmp_list);
        $tmp_header_map = array();
        foreach ($header_map as $field => $format) {
            if(is_array($format)){
                $tmp_header_map[$field] = $format['title'];
            }else{
                $tmp_header_map[$field] = $format;
            }
        }

        $header_map = $tmp_header_map;
        $list = $tmp_list;
        unset($tmp_header_map, $tmp_list);

        return self::export($header_map, $list, $filename, $download);
	}
	
	/**
	 * 导出xls文件，将数组转换成Excel文件，原始基础方法
	 * 
	 * @param array 	$header_map	导出文件Excel标题HashMap，对应字段名=>别名，不支持格式化
	 * @param array 	$list 		待导出的原始数据列表
	 * @param string	$filename	导出的文件名，当$download=false时，$filename需要具体到保存的路径
	 * @param boolean   $download   是否导出为下载，默认是
	 * @return 下载文件 或者 excel文件名
	 */
	public static function export($header_map, $list, $filename='', $download=true){
		
		$filename = empty($filename) ? time() : iconv('utf-8', 'gb2312', $filename);
			
		// 载入PHPExcel入口文件
		vendor('PHPExcel.PHPExcel', '', '.class.php');
		
		$phpExcel 	= new PHPExcel();
		$writer 	= new PHPExcel_Writer_Excel5($phpExcel);
		$phpExcel->setActiveSheetIndex(0);
		$sheet 		= $phpExcel->getActiveSheet();
		$sheet->setTitle('sheet1');
			
		$i = $chr = ord('A');
		$ls = ord('Z');
		$pre = '';
		foreach($header_map as $vo){
			if ($chr > $ls){
				$chr = ord('A');
				$pre = chr($chr+ceil($i/$ls)-1);
			}
			$sheet->setCellValue($pre.chr($chr).'1', is_array($vo) ? $vo['title']: $vo);
			
			// 设置宽度 
			$sheet->getColumnDimension($pre.chr($chr))->setAutoSize(true);
			$chr++;
			$i++;
		}
			
		if ($list && is_array($list)){
			$code = 2;
			foreach ($list as $vo){
				$j = $chr = ord('A');
				$pre = '';
				
				foreach($header_map as $key => $v){
					if ($chr > $ls){
						$chr = ord('A');
						$pre = chr($chr+ceil($j/$ls)-1);
					}
					$vo[$key] = isset($vo[$key]) ? $vo[$key] : '';	
					
					$sheet->setCellValue($pre.chr($chr).$code, $vo[$key]);
					
					$chr++;
					$j++;
				}
				
				$code++;
			}
		}
		unset($list);
			
		// 下载Excel表格
		if ($download){
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-Disposition:inline;filename="'.$filename.'.xls"');
			header("Content-Transfer-Encoding: binary");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: no-cache");
			$writer->save('php://output');
		}else{
			// 保存表格
			$filename = iconv('gb2312', 'utf-8', $filename);
			$writer->save($filename.'.xls');
			return $filename.'.xls';
		}
	}
	
}

/**
 * TODO:
 * 1. 导出Excel单元格宽度自适应内容
 */
?>