<?php
header("Content-type: text/html; charset=utf-8");
include "ExcelUtil.class.php";

// export:
$list = array(
    array('id'=>1, 'name'=>'蔡繁荣', 'age'=>25, 'percent'=>99, 'wage'=>1.50, 'status'=>1, 'create_time'=>1430209609),
    array('id'=>2, 'name'=>'蔡繁荣', 'age'=>1,  'percent'=>98, 'wage'=>1, 'status'=>2, 'create_time'=>1430209609),
);

excel_export('user', $list, '用户名单列表'); // 导出"用户名单列表.xls"文件
exit;

// import:
$excel_file = 'ExcelUtil/用户名单列表.xls';

$user_list = excel_import('user', $excel_file);
print_r($user_list);
exit;



/**
 * 定义公共函数
 */

/**
 * 导出xls文件，将数组转换成Excel文件，适配器模式包装，支持表头格式化内容
 * // $header_map
 * return array( 
 *      'id'          => 'ID',
 *      'name'        => '姓名',
 *      'age'         => '年龄',
 *      'percent'     => array('title'=>'坚持率',  'sprintf' => "%d%%"),
 *      'wage'        => array('title'=>'工资',    'number_format' => 2), // 2 or 0 默认为., number_format($number, 2, '.', '')
 *      'status'      => array('title'=>'状态',    'enum' => array(1=>'正常', 2=>'锁定')),
 *      'create_time' => array('title'=>'创建时间', 'date'=>'Y-m-d H:i:s'),
 * );
 * 
 * @param string    $header_config_filename $header_config配置文件，目录默认为LIB_PATH.'Export/'.???.'.php'
 * @param array     $list                   待导出的原始数据列表
 * @param string    $filename               导出的文件名，当$download=false时，$filename需要具体到保存的路径
 * @param boolean   $download               是否导出为下载，默认是
 * @return 下载文件 或者 excel文件名
 */
function excel_export($header_config_filename, $list, $filename, $download=true){
    // 因为需要将header配置文件放到当前项目的目录中，无法解耦，所以使用公共函数实现
    import('@.ORG.Util.ExcelUtil');

    // 将表头配置文件放到独立文件里面，直接通过文件名，来获取得到 include 'xxx.php'，可自定义路径
    $header_map = include 'ExcelUtil/'.$header_config_filename.'.php';

    //装饰器模式
    return ExcelUtil::exportDecorator($header_map, $list, $filename, $download);
}

/**
 * 导入xls文件，将Excel文件转换成数组，适配器模式包装，支持表头配置文件组装row
 * 依赖 import()方法
 * // $header_map
 * return array( 
 *      'id'          => 'ID',
 *      'name'        => '姓名',
 *      'age'         => '年龄',
 *      'percent'     => array('title'=>'坚持率',  'sprintf' => "%d%%"),
 *      'wage'        => array('title'=>'工资',    'number_format' => 2), // 2 or 0 默认为., number_format($number, 2, '.', '')
 *      'status'      => array('title'=>'状态',    'enum' => array(1=>'正常', 2=>'锁定')),
 *      'create_time' => array('title'=>'创建时间', 'date'=>'Y-m-d H:i:s'),
 * );
 * 
 * @param array     $header_map  导出文件Excel标题HashMap，对应字段名=>别名，支持格式化!
 * @param string    $excel_file  xls文件
 * @return array    $list        导出的原始数据列表
 */
function excel_import($header_config_filename, $excel_file){
    // 因为需要将header配置文件放到当前项目的目录中，无法解耦，所以使用公共函数实现
    import('@.ORG.Util.ExcelUtil');
    
    // 将表头配置文件放到独立文件里面，直接通过文件名，来获取得到 include 'xxx.php'，可自定义路径
    $header_map = include 'ExcelUtil/'.$header_config_filename.'.php';

    return ExcelUtil::importDecorator($header_map, $excel_file);
}
?>