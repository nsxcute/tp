<?php

namespace app\index\Controller;

use think\Cache;
use think\Config;
use think\Db;
use think\Loader;
use think\Controller;

class Testswoole extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    //dbindscenes表model
    protected  $deviceInfoModel;

    //Unityjson 表model
    protected $unityJsonModel;

    protected $arrContextOptions = array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    public function unitySocketRequestStart()
    {
        //curlPOST目标地址
        $url = "192.168.30.159:5601/socket.php";
        //获取所有的场景信息
        //定义数据存储数组
        $arr = Db::table('test')->select();
        $requestString = json_encode($arr);
        //curl执行post
        $result = $this->doCurlPostRequest($url , $requestString , 60);
        //dump($result);die();
        return $result;
    }

    //curl
    protected function doCurlPostRequest($url,$requestString,$timeout = 5){
        if($url == '' || $requestString == '' || $timeout <=0){
            return false;
        }
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POSTFIELDS, $requestString);
        curl_setopt($con, CURLOPT_POST,true);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT,(int)$timeout);
        return curl_exec($con);
    }


}