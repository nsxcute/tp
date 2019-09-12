<?php
namespace app\index\model;
use think\Model;
use Elasticsearch\ClientBuilder;

class User extends Model
{
	//关闭自动对应
	protected $autoCheckFields = false;
	protected $elkclient = null;   
	public function __construct()
	{ 
		//连接ip端口
		$hosts = ['192.168.30.216:9200'];  
		$this->elkclient = ClientBuilder::create()->setHosts($hosts)->build();  
	}
	public function add($params)
	{
		//var_dump($params);die;
		if($params){
			$param = [
			    'index' =>  'mass',//索引
			    'type' =>  'my_test',//类型
			    'body' => [
			    	"account_number" => $params['account_number'],
				    "balance" => $params['balance'],
				    "firstname" => $params['firstname'],
				    "lastname" => $params['lastname'],
				    "age" => $params['age'],
				    "gender" => $params['gender'],
				    "address" => $params['address'],
				    "employer" => $params['employer'],
				    "email" => $params['email'],
				    "city" => $params['city'],
				    "state" => $params['state'],
			    	'height' => $params['height'],
			    	'createtime' =>time()
			    ],  //数据
			];
			// 先查询看是否存在
			 // $res = $this->elkclient->search($data);
			// if('content' != $params['content']){
				$result = $this->elkclient->index($param);
				return json_encode($result);
			// }
		}
	}
	public function inquiry($param)
	{
		$params = [
			'index' => 'mass',
			'type' => 'my_test',
			'body' => [
				'query' => [
					'bool' => [
						'must'=>[
							'match' =>[
							    'content' => $param['content']
							]
						],
						// 一个需要一个不需要
						// 'must_not'=>[
						// 	'match_phrase' =>[
						// 	    'content' => $param['content']
						// 	]
						// ]
					],
				],
				'sort' =>[
					'createtime' => [
					 	'order' => 'desc'
					]
				],
			
			]
		];
		$result = $this->elkclient->search($params);	
		return json_encode($result);
	}
}