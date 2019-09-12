<?php
/*
	$username  用户名
	$content   留言内容
	$createtime 留言时间
*/
namespace app\index\controller;
use think\Controller;
use app\index\model\User as UserModel;
use Elasticsearch\ClientBuilder; 
class User extends Controller
{
	public $user;
	public function __construct()
	{
		parent::__construct();
		$this->user = new UserModel();
	}
	//添加数据
	public function add()
	{
		if(input('post.')){
			$params = input('post.'); 
			$result = $this->user->add($params); 
			//var_dump($result);
		    if($result){
		        $this->success("添加成功!");
		    }
		}
	}
	//查询数据
	public function inquiry()
	{
		// dump(input('post.'));die;
		$params = input('post.');
		$result = $this->user->inquiry($params); 
		var_dump($result);
	}
	//游标查询
	public function test()
	{
		$client = ClientBuilder::create()->setHosts(['192.168.30.216:9200'])->build();
    	$params = [
		    'index' => 'mass',
		    'type' => 'my_test',
		    'body' => [
		    	"query" => [
			        "bool" => [
			            "must" => [
			                [
			                    "match" => [ "username" => "张三" ]
			                ],
			                [
			                    "match" => [ "content" => "学习" ]
			                ]
			            ]
			        ]
			    ]
		    ]
		];

		$response = $client->search($params);
		var_dump($response);
	}

}