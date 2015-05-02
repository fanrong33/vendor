<?php
// $header_map = 
return array(
    'id'          => 'ID',
    'name'        => '姓名',
    'age'         => '年龄',
    'percent'     => array('title'=>'坚持率', 'sprintf' => "%d%%"),
    'wage'        => array('title'=>'工资', 'number_format' => 2),
    'status'      => array('title'=>'状态', 'enum' => array(1=>'正常', 2=>'锁定')),
    'create_time' => array('title'=>'创建时间', 'date'=>'Y-m-d H:i:s'),
);

?>