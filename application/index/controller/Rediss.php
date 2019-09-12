<?php

// +----------------------------------------------------------------------
// | Description: redis 缓存使用
// +----------------------------------------------------------------------
// | Author: phpAndy <383916522@qq.com>
// +----------------------------------------------------------------------

namespace app\index\controller;
use think\Controller;
use think\cache\driver\Redis;
use think\Db;
class Rediss extends Controller
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
		$redis = new \Redis();
		$redis->connect('127.0.0.1',6379);             
    	$result = $redis->set('test',"11111111111");
    	$result = $redis->get('test');
    	//var_dump($redis->lpush("test","111")); die;     
    	//var_dump($result);die;
    	$vo = Db::table('kwc_user')
             ->select();
        $redis->set('listtest',$vo,0);
        $res = $redis->get('listtest');
        //var_dump($res);

        $arr = array('h','e','l','l','o','w','o','r','l','d');
 
		foreach($arr as $k=>$v){
		 
		  $redis->lpush("mylist",$v);
		 
		}
		$res = $redis->lrange('mylist',0,-1);
		// var_dump($res);die;
		//缓存的使用
		$lists = [
			'name'=>'牛二',
			'sex'=>'女',
			'age'=>'22',
		];
		//判断redis缓存是否存在，如果存在就取缓存，不存在放入缓存中
		if($this->redis->exists('list_css')){
			echo 111;
			echo '<pre/>';
			print_r(json_decode($this->redis->get('list_css')));
		}else{
			if($lists){
				echo 222;
				$this->redis->set('list_css',json_encode($lists));
			}else{
				echo 333;
				echo '获取数据失败';
			}
		}
	}
	//set  无序集合
    public function set()
    {
    	//set  的使用   smembers获取
    	$r = $this->redis->sadd('skey1','sv1','sv2',44,55);
    	$r = $this->redis->sadd('skey2','svs','svg',455,878,31,44,55);
    	//移除集合skey1中的一个或多个元素
    	$r = $this->redis->srem('skey1',44,22,33); 
    	//返回集合skey1的所有成员                      
        $p = $this->redis->smembers('skey1');
        //返回集合skey1的所有成员                             
        $p1 = $this->redis->smembers('skey2'); 
        //判断sfsdf元素是否是集合skey1的成员                           
        $p = $this->redis->sismember('skey1','44');   
        //将sv1元素从skey1集合移动到skey2集合                    
        $p = $this->redis->smove('skey1','skey2','sv1');   
        //移除并返回集合中的一个随机元素               
        $p = $this->redis->spop('skey2');  
        //返回集合中的一个随机元素（不移除）                               
        $p2 = $this->redis->srandmember('skey2'); 
        //取到多个集合的交集                        
        $p2 = $this->redis->sinter('skey1','skey2');
        //取到多个集合的并集                      
        $p3 = $this->redis->sunion('skey1','skey2'); 
        //取到多个集合的差集                     
        $p4 = $this->redis->sdiff('skey1','skey2');  
        //等同于sinter，并把结果放到target_skey集合中。                     
        $p5 = $this->redis->sinterstore('target_skey','skey1','skey2'); 
        //返回集合skey1的所有成员  
        $p6 = $this->redis->smembers('target_skey');
    }
    //list 列表是简单的字符串列表，按照插入顺序排序。你可以添加一个元素到列表的头部（左边）或者尾部（右边）
    public function lists()
    {
    	//队列  list
		$msg = 'Message';
		//rpush  将一个或多个值 value 插入到列表 key 的表尾(最右边)
		//lpush  命令将一个或多个值插入到列表头部
		$this->redis->rpush($msg,json_encode(['uid'=>1,'name'=>'叶傻子']));
		$this->redis->rpush($msg,json_encode(['uid'=>2,'name'=>'叶大傻子']));
		$this->redis->rpush($msg,json_encode(['uid'=>3,'name'=>'叶二傻子']));
		//lrange 指定区间内的元素 
		$count = $this->redis->lrange($msg,0,-1);
		echo '<pre/>';
        echo"当前队列数据为：";
        print_r($count);
        //lpop  移除并返回列表 key 的头元素    
        $this->redis->lpop($msg);
        echo '出列成功';
        $count = $this->redis->lrange($msg,0,-1);
        print_r($count);
        echo '<pre/>';
        echo"当前队列数据为：";
        print_r($count);    
        //$this->Redis->delete($msg);
    }
    //zset(sorted set：有序集合)
    public function zset()
    {
    	$r = $this->redis->zadd('zkey1',3,'aa',1,'bb',10,'cc1',7,'ee1');
        $r = $this->redis->zadd('zkey2',1,'aa2',4,'bb2',6,'cc3',8,'ee2');
        $r = $this->redis->zadd('zkey3',9,'aa3',2,'bb3','2.6','ee1','0.5','ee3');
 		
 		//移除有序集合zkey1中的一个或多个成员
        $p = $this->redis->zrem('zkey1','aa','bb');  
        //返回有序集合key的基数                     
        $p = $this->redis->zcard('zkey1');  
        //返回有序集合zkey1中source值在0到5之间的成员数量                              
        $p2 = $this->redis->zcount('zkey1',0,5); 
        //为有序集合zkey1的cc1成员的score加上增量3                         
        $p2 = $this->redis->zincrby('zkey1',3,'cc1');
        //返回有序集合中cc1成员的score值                     
        $p3 = $this->redis->zscore('zkey1','cc1'); 
        //返回score值在区间的成员（从小到大）zrevrangebyscore是从大到小                       
        $p3 = $this->redis->zrangebyscore('zkey1',0,5); 
        //返回cc1成员的排名（从小到大）                  
        $p3 = $this->redis->zrank('zkey1','cc1');
        //返回cc1成员的排名（从大到小）                         
        $p4 = $this->redis->zrevrank('zkey1','cc1'); 
         //移除指定排名(相当于按下标删除)区间内的所有成员，返回被移除成员的数量，zremrangebyscore按score值区间移除                     
        $p3 = $this->redis->zremrangebyrank('zkey1',0,3); 
        //返回指定区间成员，顺序从小到大               
        $p1 = $this->redis->zrange('zkey1',0,5);
        //返回指定区间成员，顺序从小到大                          
        $p2 = $this->redis->zrange('zkey2',0,5);
        //返回指定区间成员，顺序从小到大                          
        $p3 = $this->redis->zrange('zkey3',0,5); 
        //返回指定区间成员,顺序从大到小                         
        $p2 = $this->redis->zrevrange('zkey1',0,5);  
        //计算交集                     
        $p4 = $this->redis->zinterstore('target_zkey',['zkey2','zkey3']);
        //计算并集 
        $p4 = $this->redis->zunionstore('target_zkey',['zkey2','zkey3']); 
    }

    //Hash（哈希）  赋值与取值：HSET/HMSET 
    public function hash()
    {
    	//添加元素
    	$this->redis->hSet('hash', 'key1', 'val1');
    	// 获取 hash表 中键名是 key1 的值
    	$this->redis->hGet('hash', 'key1');
    	// 获取 hash表的元素个数
    	$this->redis->hLen('hash');
    	// 获取 hash表 中所有的键
    	$this->redis->hKeys('hash');
    	// 获取 hash表 中所有的值
    	$this->redis->hVals('hash');
    	// 判断 hash 表中是否存在键名是 key2 的元素
    	$this->redis->hExists('hash', 'key2');
    	// 批量添加元素
    	$this->redis->hMset('hash', ['key2' => 'val2', 'key3' => 'val3']);
    	// 批量获取元素
    	$this->redis->hMGet('hash', ['key1', 'key2', 'key3']);
    	//删除 hash表
    	$this->redis->delete('hash');
    }
    //string 
    public function strings()
    {
    	  //监视一个或多个key，如果在执行事物之前这个key被改动（可能有多个客户端在操作这个key）,事物将被打断
    	  $this->redis->watch('key');
    	  //用于标记一个事物块的开始
          $this->redis->multi();
 
          $this->redis->set('string1','s_value1');
          $this->redis->get('string1');
          $this->redis->exists('apple');
          $this->redis->setnx('apple','my_apple');
          $this->redis->setnx('apple','my_apple2');
          $this->redis->get('apple');

          //删除数据
    	  del('key')
    	  
 		  //取消监视
          $this->redis->unwatch('key');
          //取消事物
          $this->redis->discard();
          //执行事物,返回每条命令执行结果组成的数组
          $e = $this->redis->exec(); 
          pr($e);
          /*Array
                (
                    [0] => 1
                    [1] => s_value1
                    [2] => 1
                    [3] =>
                    [4] =>
                    [5] => my_apple
                )
           * */
          $this->redis->lpush('jjkey',111,222,333);
          echo $this->redis->type('jjkey').'<br><hr>';
          pr($this->redis->lrange('jjkey',0,5));
          $this->redis->set('jjkey','dsfffffffffffffffff');
          echo $this->redis->get('jjkey').'<br><hr>';
          echo $this->redis->type('jjkey').'<br><hr>';
 		  //把key的值设置为3秒，并将key的生存时间设为3秒
          $this->redis->setex('str11',3,'vvv1');
          $this->redis->set('str22','vvv22222');
          //从偏移量开始填充字符串，会覆盖原来的
          $this->redis->setrange('str22',20,'ccc222');
          //vvv22222ccc222
          var_dump($this->redis->get('str22'));
        # 情况2：对空字符串/不存在的ke  y进行SETRANGE
        $this->redis->EXISTS('empty_string');//bool(false)
        $this->redis->SETRANGE('empty_string', 5 ,"Redis!");  # 对不存在的key使用SETRANGE //int(11)
        var_dump($this->redis->get('empty_string'));  # 空白处被"\x00"填充  #"\x00\x00\x00\x00\x00Redis!"   //return string(11) "Redis!"
        $a = ['s1'=>'v1','s2'=>'v2'];
        $this->redis->mset($a);//同时设置一个或多个key-value对，会覆盖原来已存在的key值，如果想不覆盖，需要使用msetnx
        pr($this->redis->mget(['s1','s2']));
        //======================================SETBIT===========================================//
        $bit_val=67;
        echo decbin($bit_val).'<br>'; //1000011
        var_dump($this->redis->SETBIT('bit',1,1));//int(0)  空位上都是0
        var_dump($this->redis->SETBIT('bit',2,0));//int(0)
        var_dump($this->redis->SETBIT('bit',3,0));//int(0)
        var_dump($this->redis->SETBIT('bit',4,0));//int(0)
        var_dump($this->redis->SETBIT('bit',5,0));//int(0)
        var_dump($this->redis->SETBIT('bit',6,1));//int(0)
        var_dump($this->redis->SETBIT('bit',7,1));//int(0)
 
        var_dump($this->redis->GET('bit')); //string(1) "C" ,二进制为：1000011 ,ASCII:67
 
        var_dump($this->redis->GETBIT('bit', 6 )); //int(1)  取出第6位（从左到右）为“1”
 
        var_dump($this->redis->SETBIT('bit',5,1));//int(0)  把第5位的0改为1
        var_dump($this->redis->SETBIT('bit',6,0));//int(1)  把第6位的1改为0
 
        var_dump($this->redis->GET('bit')); //string(1) "E ,二进制为：1000101,ASCII:69l
        var_dump(decbin(ord($this->redis->GET('bit')))); //1000101
        $key = $this->redis->randomkey();//随机返回一个key
        $p = $this->redis->move('s1',0);//移动指定的key到db库中去
        $p = $this->redis->rename('s2','new_s2');
        $p = $this->redis->object('ENCODING','new_s2');
        $this->redis->lpush('website2',30,'1.5',44,15);
        $this->redis->flushall();
 
 
       $redis_sort_option = [
                'by'   => 'user_level_*',#按这个key来排序
                'sort' => 'desc',
                'get'  => ['#','user_name_*','user_level_*'],//#代表输出原有的key-user_id
                'store'=> 'target2'
       ];
        $p = $this->redis->SORT('user_id',$redis_sort_option);
        $this->redis->expire('target2',6);
 
        $p2 = $this->redis->lrange('target2',0,20);
        $p = array_chunk($p,3);
        pr($p);
        $p = $this->redis->ping();#测试时候使用
        $p = $this->redis->echo('sssssssssssssssss');#测试时候使用
          #quit 关闭连接
        $p = $this->redis->LASTSAVE();//返回最后一次操作成功的时间，以unix时间戳表示
        $p = date('Y-m-d H:i:s',$p);
        $p = $this->redis->dbsize();  //返回当前数据库的key的数量
        $p = $this->redis->shutdown();
        $p = $this->redis->info();    //返回redis服务器的各种统计信息和统计值
        $p = $this->redis->monitor(); //报错
        pr($p);
 
        $this->redis->hmset('test',['name'=>'tank','sex'=>"man"]);
        pr( $this->redis->sMembers('citys'));
 
        /*Array(
             [name] => tank
             [sex] => man
        )*/
    }
}