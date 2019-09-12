<?php
define("WEB_PATH" , "http://192.168.30.159");
//实例化swoole对象
$ws = new swoole_websocket_server("192.168.30.159", 12202);

$ws->on('open' , function ($ws , $request){
	var_dump("客户端-{$request->fd}连接");
	$stats = $ws->stats();
	// var_dump($stats);
	$connectionNum = $stats['connection_num'];
	execCrontab($connectionNum, 1);
});


$ws->on('message', function( $ws , $request ) {
    
	if($request->data == "heartbeat") {
             $ws->push($request->fd, "I am still alive");
        }else {
			go(function () use ($ws , $request){
				$GLOBALS[$request->fd] = swoole_timer_tick(5000, function ($timer_id) use ($ws , $request){
					$redis = new Redis();
                    $redis->pconnect('127.0.0.1', '6379');

					try{
						//var_dump($request->data);
						$result = isJson($request->data , true);
						//var_dump($result);
						$id  =  $result["sid"];

						$data = $redis->hGetAll(getRedisKeyByApi($id, 'screen'));
						foreach($data as &$val) {
							$val = json_decode($val, true);
						}
						$curlData = '';
						if ($data) {
							$curlData = json_encode(['err'=>0, 'data'=>$data], JSON_UNESCAPED_UNICODE);
						}
						if (!isset(json_decode($curlData, 1)["data"])) {
                            $curlData = file_get_contents(WEB_PATH."/index/Websocket/index?id=".$id);
                        }
						$ws->push($request->fd, $curlData);	
					} catch (\Exception $e) {
	
					}
				//var_dump($user_message);
				});	   
			});
		}
});

function isJson($data = '', $assoc = false) {
    $data = json_decode($data, $assoc);
    if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
        return $data;
    }
    return false;
}
// 将api链接转换加密成rediskey 2019-06-03 by 李福龙
function getRedisKeyByApi($key, $prefix = '')
{
    // dump($apiUrl);
    $key = $prefix.$key;
	$api = trim($key);
	$apiRedisKey =  '2019apiRedisKey';
	return md5($apiRedisKey.md5($key).'openv');
}
// 执行计划任务 $type,0关闭，1打开
function execCrontab($num, $type)
{
	$cronTime = '* * * * * ';
	$shellCommand = 'sh  /home/wwwroot/lifulong_state-power/application/middle/controller/Cronque.sh';
	// 计划任务所属用户
	$cronUser = 'www';
	// 待匹配字符串（用来匹配成功，将整行删除）
	$pregToDelStr = 'Cronque.sh';
	// 计划任务路径
	$cronFile = __DIR__.'/cron_www';
	$cronQue = $cronTime.$shellCommand;
	$sedFile = 'sed -i /'.$pregToDelStr.'/d '. $cronFile.' && crontab -u '.$cronUser.' '.$cronFile;
	// 0取消队列 1加入队列
	if ($num === 0 && $type == 0) {
		// 删除计划任务，并重新执行
		$command = $sedFile;
		// echo $command;
		execCommand($command);
	} elseif ($num === 1 && $type == 1) {
		// 用户计划任务列表,写入文件
		$command = '(crontab -l -u  '.$cronUser.' > '.$cronFile.';'.$sedFile.' ; echo -e "'.$cronQue.'" > '.$cronFile.' ; crontab -u '.$cronUser.' '.$cronFile.' ;'.$shellCommand. ' > /dev/null 2>&1 &)';
		// echo $command;
		execCommand($command);
	} 
	// echo $num;
}
// php exec执行命令
function execCommand($command)
{	
	exec($command, $output, $return_var);
	// 返回布尔值
	return !$return_var;
}

$ws->on('close',function($ws,$request){
	if(isset($GLOBALS[$request])) {
		$result = swoole_timer_clear($GLOBALS[$request]);
		 var_dump($result);
	}
	var_dump("客户端-{$request}断开连接");
	$stats = $ws->stats();
	// var_dump($stats);
	$connectionNum = $stats['connection_num'] - 1;
	execCrontab($connectionNum, 0);
});

$ws->start();