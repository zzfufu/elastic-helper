<?php
//https://www.elastic.co/guide/cn/elasticsearch/php/current/_index_management_operations.html

namespace zzfufu\ZzElastic;

class Mapping
{
    protected $client = null;
    protected SetIndex $setIndex;

    public function __construct(\Elasticsearch\Client $connection, SetIndex $setIndex)
    {
        $this->client = $connection;
        $this->setIndex = $setIndex;
    }

    /**
     * 初始化索引参数
     * @author joniding
     * @return array
     */
    public function initParams()
    {
        return $this->setIndex->toArray();
    }

    /**
     * 更新索引的映射 mapping
     * @author joniding
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function setMapping($data)
    {
        try{
            $initParams = $this->initParams();
            $initParams['body'] = $data;

            $res = $this->client->indices()->putMapping($initParams);
        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }

    /**
     * 获取索引映射 mapping
     * @author joniding
     * @return array
     * @throws \Exception
     */
    public function getMapping()
    {
        try{
            $initParams = $this->initParams();
            $res = $this->client->indices()->getMapping($initParams);
        }catch (\Exception $e){
            throw $e;
        }

        return $res;
    }
}