<?php

namespace app\index\controller;
use think\Controller;
use think\cache\driver\Redis;
use think\Db;
use ZipArchive;
class Zip extends Controller
{
    public function index()
    {
        $path = ROOT_PATH.'public/download/1.php';
        $filename = '1php';
        $this->zip_download($path,$filename);
    }
    public function zip_download($path, $filename)
    {
        // 最终生成的文件名（含路径）
        $filename = ROOT_PATH . 'public/download/' . $filename . '.zip';
        // 如果存在压缩文件，删除
        if (file_exists($filename)) {
        	unlink($filename);
        }
        //重新生成文件
        $zip = new ZipArchive();
        if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
        exit('无法打开文件，或者文件创建失败');
        return 0;
        }
        // 要压缩的文件
        $datalist = $path;
        $zip->addFile($datalist);
        // 遍历添加待压缩文件
        // foreach ($datalist as $val) {
        // $p = ROOT_PATH . 'Public/' . $val;
        // if (file_exists($p)) {
        // //val:qrcode/1_about.png
        // $zip->addFile($val);
        // }
        // }
        // 关闭
        // $zip->close();
        // if (!file_exists($filename)) {
        // // 即使创建，仍有可能失败
        // exit('无法找到文件');
        // return 0;
        // }
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="messages.20171015.zip"');
        readfile($filename);
    }

    /**
     * 功能：压缩文件并下载函数
     * files: 需要压缩的文件，destination：压缩包名，overwrite：是否使用覆盖创建
     * 注意：要求php5.0+  zip扩展1.7.0+
    */
    public function test()
    {
        $path = ROOT_PATH.'public/download/1.php';
        $filename = '1php';
        $this->create_zip($path,$filename,$overwrite = false);
    }
    public function create_zip($files = array(),$destination = '',$overwrite = false)
    {
        //判断文件夹是否已存在且覆盖创建为否
        if(file_exists($destination) && !$overwrite) { 
            return false; 
        }
     
        $valid_files = array();
        //安全处理
        if(is_array($files)) {
            foreach($files as $file) {
                //确认文件是否存在
                if(file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }
        //得到要压缩的文件后
        if(count($valid_files)) {
            //使用zip函数，如果zip已存在就覆盖打开，不存在就创建打开
            $zip = new ZipArchive();
            if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            //向zip文件夹中追加压缩文件
            foreach($valid_files as $file) {
                //addFile（文件绝对路径，新文件名）
                $zip->addFile($file,$file);
            }
            
            //关闭zip函数
            // $zip->close();
            // //下载前判断文件是否打包
            // if(!file_exists($destination)){
            //     message('文件夹不存在', '', 'error');
            // }
     
            //下载zip
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename='.basename($destination)); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: '. filesize($destination)); //告诉浏览器，文件大小
            readfile($destination);
            exit();
        }else{
            return false;
        }
    }
    
}