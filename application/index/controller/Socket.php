<?php

class MyWebsocket {

    private $server;
    private $fid=[];
    # run()
    public function toRun() {
        $this->server = new swoole_websocket_server("192.168.30.159", 5601, SWOOLE_BASE, SWOOLE_SOCK_TCP); //SWOOLE_SSL  需要ssl才加
        #监听WebSocket连接打开事件
        $this->server->on('open', function ($server, $request) {
            $this->server->push($request->fd, "hello, welcome ID:{$request->fd}\n");
            $this->fid[]=$request->fd;   # $request->fd fd
        });
        #监听WebSocket消息事件
        $this->server->on('message', function ($server, $frame) {   #$frame->data 消息内容
            $msg = 'from' . $frame->fd . ":{$frame->data}\n";
            foreach ($this->fid as $fd) {
                $server->push($fd, $msg);
            }
        });

        //监听WebSocket连接关闭事件
        $this->server->on('close', function($ws, $fd) {
            $fd_key = array_search($fd, $this->fid ? $this->fid : []);
            $key_zero = isset($this->fid[0]) && $this->fid[0] == $fd ? TRUE : FALSE;  # key=0
            if ($fd_key || $key_zero) {
                unset($this->fid[$fd_key]);
            }
            echo "client-{$fd} is closed\n";
        });

        #onRequest回调    http://127.0.0.1:9502/?sendto=1,20,3&message=%E4%BD%A0%E5%A5%BD
        $this->server->on('request', function ($req, $respone) {
            $data = $req->rawContent();
            //var_dump($data);
            $fileArr = json_decode($data);
            # get 两个参数, userid ","  发送消息
            $list=[];
            if (isset($req->get['sendto']) && isset($req->get['message'])) {
                $user = explode(',', $req->get['sendto']);
                $list = array_intersect($this->fid, $user);
                if (!empty($list)) {
                    foreach ($list as $fd) {
                        $this->server->push($fd, $req->get['message']);
                    }
                }
            $this->server->push($fd,'lal');
            }
            $total= count($this->fid);
            $sendSum= $data;
            //dump($sendSum);
            $respone->end("Current fid:{$respone->fd},  OnLine:{$total}, Send:{$sendSum}");
        });
        $this->server->start();
    }
}
$app = new MyWebsocket();
$app->toRun();
