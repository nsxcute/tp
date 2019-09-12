<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class My extends Controller
{
	public function index()
	{
		$sql = 'show tables';
		$res = Db::table('riped_ip_list')->select();
		//var_dump($res);die;
		$tables_in_db = Db::query($sql);
		// $tables = array();
		// foreach($tables_in_db $key => $value){
		// 	$tables[$key] = $value['Tables_in'.$dbname];
		// }
		//var_dump($tables_in_db);die;

		$sql = "show create table `riped_insp_contacts`";//tableName 要查看的表名
		$create_table = Db::query($sql);
		dump($create_table );
	}

	public function backups()
    {
        //1.获取数据库信息
        $info = Db::getConfig();
        //var_dump($info);die;
        $dbname = $info['database'];
        //2.获取数据库所有表
        $tables = Db::query("show tables"); 
        //3、组装头部信息
        header("Content-type:text/html;charset=utf-8");
        $path = ROOT_PATH.'data/';
        //var_dump($path);die;
        $database = $dbname;   //获取当前数据库
        //var_dump($database);die;
        $info  = "-- ----------------------------\r\n";
        $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
        $info .= "-- MySQL - 5.5.52-MariaDB : Database - ".$database."\r\n";
        $info .= "-- ----------------------------\r\n\r\n";
        $info .= "SET NAMES utf8;\r\nSET FOREIGN_KEY_CHECKS = 0;\r\n\r\n";
        //var_dump($path);die;
        //4、检查目录是否存在
        if (is_dir($path)) {
        	echo 111;
            if (is_writable($path)) {
            } else {
                echo '目录不可写'; exit();
            }
        } else {
        	echo 222;
            mkdir($path,0777,true);
        }
        //5、保存的文件名称
        $file_name = $path.$database.'_'.date('Ymd_His').'.sql';
        file_put_contents($file_name, $info, FILE_APPEND);
        echo 222;
        //6、循环表，写入数据
        foreach ($tables as $k => $v) {
            $val = $v["Tables_in_$database"];

            $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$val' AND TABLE_SCHEMA='$dbname'";
            $res = Db::query($sql);
            //var_dump($res);die;
            $max_num = Db::table("$val")->order('uid desc')->value('uid');
            //查询表结构
            $info_table = "-- ----------------------------\r\n";
            $info_table .= "-- Table structure for `$val`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            $info_table .= "DROP TABLE IF EXISTS `$val`;\r\n";
            if (count($res) < 1) {
                continue;
            }
            //echo 'ceshi';die;
            $info_table .= "CREATE TABLE `$val` (\n\r\t";
            foreach ($res as $kk => $vv) {

                   $info_table .= " `".$vv['COLUMN_NAME']."` ";
                   $info_table .= $vv['COLUMN_TYPE'];
                   //是否允许空值
                   if ($vv['IS_NULLABLE'] == 'NO') {
                       $info_table .= " NOT NULL ";
                   }
                   //判断主键
                   if ($vv['EXTRA']) {
                       $info_table .= " AUTO_INCREMENT ";
                       $key = $vv['COLUMN_NAME'];
                   }
                   //编码
                   if ($vv['CHARACTER_SET_NAME']) {
                       $info_table .= " CHARACTER SET ".$vv['CHARACTER_SET_NAME'];
                   }
                   //字符集
                   if ($vv['COLLATION_NAME']) {
                       $info_table .= " COLLATE ".$vv['COLLATION_NAME'];
                   }
                   //默认数值
                   if ($vv['COLUMN_DEFAULT']) {
                       $info_table .= " DEFAULT ".$vv['COLUMN_DEFAULT'];
                   }
                   //注释
                   if ($vv['COLUMN_COMMENT']) {
                       $info_table .= " COMMENT '".$vv['COLUMN_COMMENT']."',\n\r\t";
                   }
               }
               $info_table .= " PRIMARY KEY (`$key`) USING BTREE";
               $info_table .= "\n\r) ENGINE = MyISAM AUTO_INCREMENT $max_num CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;\r\n\r\n";
            
            //查询表数据
            $info_table .= "-- ----------------------------\r\n";
            $info_table .= "-- Data for the table `$val`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            file_put_contents($file_name,$info_table,FILE_APPEND);
            $sql_data = "select * from $val";
            $data = Db::query($sql_data);
            $count= count($data);
            if ($count < 1) {
                continue;
            }
            foreach ($data as $key => $value) {
                $sqlStr = "INSERT INTO `$val` VALUES (";
                foreach($value as $v_d){
                    $v_d = str_replace("'","\'",$v_d);
                    $sqlStr .= "'".$v_d."', ";
                }
                //需要特别注意对数据的单引号进行转义处理
                //去掉最后一个逗号和空格
                $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                $sqlStr .= ");\r\n";
                file_put_contents($file_name,$sqlStr,FILE_APPEND);
            }
            $info = "\r\n";
            file_put_contents($file_name,$info,FILE_APPEND);
            
        }
        //7、下载数据到本地
        ob_end_clean(); 
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header('Content-Description: File Transfer'); 
        header('Content-Type: application/octet-stream'); 
        header('Content-Length: ' . filesize($file_name)); 
        header('Content-Disposition: attachment; filename=' . basename($file_name)); 
        readfile($file_name); 
        DownloadFile($path.$file_name); 
        $this->success("数据已备份");
    }

}