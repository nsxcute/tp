<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;

class Highchars extends Controller
{
	public function highchars()
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
			                ]
			            ],
			            "filter" => [
			            	"rang" => [
			            		"blance" => [
			            			"height" => 100
			            		]
			            	]
			            ]
			        ]
			    ],
		    ]
		];
		$response = $client->search($params);
		$responses = $response['hits']['hits'];
		foreach ($responses as $key => $value) {
			$res[] = $value['_source']['height'];

		    $data = json_encode($res);
			// var_dump($data);
			$this->assign('data',$data);
		}
		// die;
		return $this->fetch();
	}
	//统计求和
	public function total()
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
			                ]
			            ],
			        ]
			    ],
			    "aggs"=>[
			        "total" => [
			        	"stats"=>[
			        		"field"=>"age"
			        	]
			        ]
			    ]
		    ]
		];
		$response = $client->search($params);
		var_dump($response);
	}
}
