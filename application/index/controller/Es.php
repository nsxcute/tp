<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;

class Es extends Controller
{
    // 节点链接
    protected $nodeUrl = '';
    // 备份目录
    protected $outputPath = '';
    //查询语句
    protected $queryStr = '';
    // 每个索引的每次备份数
    protected $everyTimeCount = 500000;
    // 每次备份的个数
    protected $limit = 5000;
    // 保存的几个月的数据 (默认6个月)
    protected $saveMonth = 6;
    // es对象
    public $client;
    // es主机地址
    public $hsots;
    // 索引名称
    protected $indicesName;
    // 索引uuid
    protected $indicesUuid;
    // 备份工具命令elasticdumpPath所在的路径
    protected $elasticdumpPath = '';

    public function __construct()
    {
    	$hosts = [
            '192.168.30.161',
        ];
        $this->nodeUrl = ClientBuilder::create()->setHosts($hosts)->build();
        var_dump($this->nodeUrl);die;
        $this->nodeUrl = rtrim($this->nodeUrl,'/').'/';
        $this->outputPath = \think\Config::get('business')['elasticdump_path'];
        $this->elasticdumpPath = \think\Config::get('business')['elasticdump_bin_path'];
       
        ini_set('max_execution_time', -1);
    }
    public function index()
    {
    	$res = $this->getQuery();
    	var_dump($res);
    }
    protected function getQuery()
    {
        $fromDate = date('Y-m-d', strtotime('-'.$this->saveMonth.' month'));
        $fromDate = date('Y-m-d\TH:i:s\Z', strtotime($fromDate));
        $json = [
            'query' => [
                'bool'=> [
                    'must'=> [
                        [
                            'range'=> [
                                '@timestamp'=> [
                                    'gte' => $fromDate,
                                    'lte'=>'now'
                                ]
                            ]
                        ]
                    ]
                ]

            ],
        ];
        return json_encode($json);
    }
}