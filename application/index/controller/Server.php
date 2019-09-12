<?php
namespace app\index\controller;
use think\Controller;
class Server extends Controller
{
    public function index()
    {
        $ws = new swoole_websocket_server('192.168.30.72', 9501);
    }
}
