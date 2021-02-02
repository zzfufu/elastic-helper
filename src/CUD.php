<?php


namespace zzfufu\ZzElastic;

class CUD
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

    public function createIndex($settings = [])
    {
        try{
            $initParams['index'] = $this->indexName;
            !empty($settings) && $initParams['body']['settings'] = $settings;

            $res = $this->client->indices()->create($initParams);

        }catch(\Exception $e){
            throw $e;
        }

        return $res;
    }

    /**
     * 向索引中插入数据
     * @author joniding
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        try{
            $params = $this->initParams();
            isset($data['id']) && $params['id'] = $data['id'];
            $params['body'] = $data['body'];

            $res = $this->client->index($params);
            var_dump($res);
        }catch (\Exception $e){
            throw $e;
        }
        if (!isset($res['_shards']['successful']) || !$res['_shards']['successful']){
            return false;
        }
        return true;
    }

    /**
     * 批量插入数据
     * @author joniding
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function bulk($data)
    {
        try{
            if (empty($data['body'])) return false;
//            $params = $this->initParams();
//            $params['body'] = $data['body'];
            $params = [];
            foreach ($data['body'] as $body) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->indexName,
                        '_type' => $this->indexType,
                    ]
                ];

                $params['body'][] = $body;
            }

            $res = $this->client->bulk($params);

        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }

    /**
     * 根据唯一id删除
     * @author joniding
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        try{
            $params = $this->initParams();
            $params['id'] = $id;

            $res = $this->client->delete($params);
        }catch (\Exception $e){
            throw $e;
        }
        if (!isset($res['_shards']['successful'])){
            return false;
        }
        return true;
    }
}