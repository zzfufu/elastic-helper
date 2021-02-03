<?php

namespace zzfufu\ZzElastic;

class Setting
{
    protected $client = null;
    protected SetIndex $setIndex;

    public function __construct(\Elasticsearch\Client $connection, SetIndex $setIndex)
    {
        $this->client = $connection;
        $this->setIndex = $setIndex;
    }

    /**
     * 更改索引的配置参数（先创建一个索引）
     * $params = [
        'index' => 'my_index',
            'body' => [
                'settings' => [
                'number_of_replicas' => 0,
                'refresh_interval' => -1
            ]
        ]
    ];
     * @param $params
     */
    public function putSettings($params)
    {
        $response = $this->client->indices()->putSettings($params);
        var_dump($response);
    }

    /**
     * 获取一个或多个索引的当前配置参数
     * $params = ['index' => 'my_index'];
     * $params = [
            'index' => [ 'my_index', 'my_index2' ]
        ];
     * @param $params
     */
    public function getSettings($params)
    {
        $response = $this->client->indices()->getSettings($params);
        var_dump($response);
    }
}