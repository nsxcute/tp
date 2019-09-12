<?php

namespace app\index\controller;
use think\Controller;
class Test extends Controller
{
	public function index(){
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
		$ret = $client->connect("192.168.30.72", 9501);
		if(empty($ret)){
			echo 'error!connect to swoole_server failed';
		} else {
			$client->send('blue');
		}
	}
}
