<?php
namespace app\index\controller;
use think\Controller;
use Elasticsearch\ClientBuilder;
use think\Cache;
use think\Db;
class Estest extends Controller
{
    public function __construct()
    {
        $params = array(
            '192.168.30.97:9200'
        );
        $this->es = ClientBuilder::create()->setHosts($params)->build();
    }
    public function setMapping()
    {
        $params = [
            // 索引名称
            'index' => 'ynews',
            'body' => [
                //配置
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            //同义词
                            'my_synonym_filter' => [
                                'type' => 'synonym',
                                // 远程同义词路径（文件utf-8编码）
                                'synonyms_path' => "http://192.168.30.97/synonym.txt",
                                //同义词文件30秒刷新一次
                                "interval" => 30
                            ]
                        ],
                        'analyzer' => [
                            //配置同义词过滤器
                            'my_synonyms' => [
                                //最细粒度拆分
                                'tokenizer' => 'ik_max_word',
                                'filter' => [
                                    'lowercase',
                                    'my_synonym_filter'
                                ]
                            ]
                        ]
                    ]
                ],
                // 映射
                'mappings' => [
                    'y_doc' => [
                        //配置数据结构与类型
                        'properties' => [
                            'name' => [
                                //keyword不会被拆分
                                'type' => 'keyword',
                                'fields' => [
                                    //拼音会被拆分
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin',
                                    ]
                                ]
                            ],
                            'age' => [
                                //age是整型
                                'type' => 'integer'
                            ],
                            'content' => [
                                //text会被拆分
                                'type' => 'text',
                                //使用同义词
                                'analyzer' => 'my_synonyms',
                                'fields' => [
                                    'pinyin' => [
                                        'type' => 'text',
                                        'analyzer' => 'pinyin'
                                    ],
                                    'suggest' => [
                                        //completion用前缀搜索的特殊结构
                                        'type' => 'completion',
                                        'analyzer' => 'ik_max_word'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $res = $this->es->indices()->create($params);
        var_dump($res);
    }

}