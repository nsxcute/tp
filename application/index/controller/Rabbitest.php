<?php
/**
 * User: yuzhao
 * Description:
 */

namespace app\index\controller;

use app\common\tool\RabbitMQTool;
use think\Controller;
//require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exchange\AMQPExchangeType;
class Rabbitest extends Controller 
{
    public function __construct()
	{
		parent::__construct();
		$this->redis = new \Redis();
		$this->redis->connect('127.0.0.1',6379);
		//$this->Expire = C('REDIS_EXPIRE');
	}
    public function test() 
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');
        
        $channel = $connection->channel();
        
        $channel->queue_declare('hello', false, false, false, false);

        $msg = new AMQPMessage('Hello World!');

        $channel->basic_publish($msg, '', 'hello');

        echo " [x] Sent 'Hello World!'\n";
    }
    //判断消息队列中是否存在这个键值
    public function test1()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();     
        $channel = new AMQPChannel($connection); 
        $q = new AMQPQueue($channel);die;
        $exchange = new AMQPExchangeType($channel); 
        //dump($exchange);
        $exchange->setName('hello'); // 设定要检查的exchange的名字 
        $exchange->setType(AMQP_EX_TYPE_DIRECT); 
        $exchange->setFlags(AMQP_PASSIVE); // 使用PASSIVE参数 
        $exchange->declareExchange(); 
    }
    //发送者 send
    public function send()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();  

        $channel->queue_declare('test', false, false, false, false);

        $msg = new AMQPMessage('Hello World!');
        $channel->basic_publish($msg, '', 'test');

        echo " [x] Sent 'Hello World!'\n";
    }
    //消息接收者 receive
    public function receive()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();  
        //申明一个队列
        $channel->queue_declare('test', false, false, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        
        $callback = function ($msg) {

            echo ' [x] Received ', $msg->body, "\n";
        };
          
        $channel->basic_consume('test', '', false, true, false, false, $callback);
          
        while (count($channel->callbacks)) {
            
            $channel->wait();
        }
    }
    //修改send方法
    public function new_task()
    {
        for($i=0;$i<10;$i++){
            $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
            $channel = $connection->channel();
            //申明一个队列
            $channel->queue_declare('task_queue', false, true, false, false);     
            //$data = implode(' ', array_slice($argv, 1));
            //if (empty($data)) {
                $data = "task_queue! ".$i;
            // }
            $msg = new AMQPMessage(
                $data,
                array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            );
            //dump($msg);die;
            $channel->basic_publish($msg, '', 'task_queue');
            echo ' [x] Sent ', $data, "\n";
        }
        
    }
    //如果其中一个消费者死亡，对应的key也不存在
    public function worker()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();     
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done\n";
          };
          $channel->basic_qos(null,1,null);
          $channel->basic_consume('test', '', false, true, false, false, $callback);
          //dump($data);
        while (count($channel->callbacks)) {
            
            $channel->wait();
        }
    }
    //当消费者死亡时，RabbitMQ将重新发送消息。即使处理消息需要非常长的时间，也没关系。
    public function workers()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();     
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
          
        $channel->basic_consume('test', '', false, false, false, false, $callback);
        while (count($channel->callbacks)) {
            
            $channel->wait();
        }
    }
    //日志  一次向多个消费者发送信息
    public function emit_log()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();
        //申明一个队列
        $channel->exchange_declare('logs', 'fanout', false, false, false);

        $data = "info: Hello World!";
        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'logs');

        echo ' [x] Sent ', $data, "\n";

        //$channel->close();
        //$connection->close();
        


    }
    //消费者
    public function receive_logs()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->exchange_declare('logs', 'fanout', false, false, false);

        list($queue_name, ,) = $channel->queue_declare("", false, false, false, false);

        $channel->queue_bind($queue_name, 'logs');

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        $callback = function ($msg) {
            //echo ' [x] ', $msg->body, "\n";
            $this->redis->setex('Rabbit',10,$msg->body);
            dump($this->redis->get('Rabbit'));
            
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        //dump($queue_name);die;
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        
        //$channel->close();
        //$connection->close();
    }
    //路由  生产者
    public function emit_log_direct()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->exchange_declare('direct_logs', 'direct', false, false, false);

        $severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';
        $argv = ["test","test1","test2"];
        $data = implode(' ', array_slice($argv, 2));
        if (empty($data)) {
            $data = "Hello World!";
        }

        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'direct_logs', $severity);

        echo ' [x] Sent ', $severity, ':', $data, "\n";

    }
    //消费者
    public function receive_logs_direct()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->exchange_declare('direct_logs', 'direct', false, false, false);

        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $argv = ["test","test1","test2"];
        $severities = array_slice($argv, 1);
        //var_dump($severities);die;
        if (empty($severities)) {
            //echo 111;
            file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
            exit(1);
        }

        foreach ($severities as $severity) {
            $channel->queue_bind($queue_name, 'direct_logs', $severity);
        }

        echo " [*] Waiting for logs. To exit press CTRL+C\n";
        //echo 111;
        $callback = function ($msg) {
            echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
    
    public function height()
    {
        dump(highlight_string("Hello world",true));
    }
    public function rediss()
    {
        dump($this->redis->get('Rabbit'));
    }

    //主题  生产者
    public function emit_log_topic()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->exchange_declare('topic_logs', 'topic', false, false, false);
        $argv = ["test","test1","test2"];
        $routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
        $data = implode(' ', array_slice($argv, 2));
        if (empty($data)) {
            $data = "Hello World!";
        }

        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'topic_logs', $routing_key);

        echo ' [x] Sent ', $routing_key, ':', $data, "\n";
    }
    //消费者
    public function receive_logs_topic()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->exchange_declare('topic_logs', 'topic', false, false, false);

        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $argv = ["test","test1","test2"];
        $binding_keys = array_slice($argv, 1);
        if (empty($binding_keys)) {
            file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
            exit(1);
        }

        foreach ($binding_keys as $binding_key) {
            $channel->queue_bind($queue_name, 'topic_logs', $binding_key);
        }

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
    //RPC  生产者
    public function rpc_server()
    {
        $connection = new AMQPStreamConnection('192.168.30.72', 5672, 'guest', 'guest');     
        $channel = $connection->channel();

        $channel->queue_declare('rpc_queue', false, false, false, false);

        function fib($n)
        {
            if ($n == 0) {
                return 0;
            }
            if ($n == 1) {
                return 1;
            }
            return fib($n-1) + fib($n-2);
        }

        echo " [x] Awaiting RPC requests\n";
        $callback = function ($req) {
            $n = intval($req->body);
            echo ' [.] fib(', $n, ")\n";

            $msg = new AMQPMessage(
                (string) fib($n),
                array('correlation_id' => $req->get('correlation_id'))
            );

            $req->delivery_info['channel']->basic_publish(
                $msg,
                '',
                $req->get('reply_to')
            );
            $req->delivery_info['channel']->basic_ack(
                $req->delivery_info['delivery_tag']
            );
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

    }
    //消费者
    public function rpc_client()
    {
        
    }

}