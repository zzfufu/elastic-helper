<?php


namespace zzfufu\ZzElastic;


use Elasticsearch\ClientBuilder;

class ElasticsearchConnection
{
    protected $config = [];
    protected $indexName = '';
    protected $indexType = '';

    protected $client = null;

    public function __construct(array $config)
    {
        $this->config = $config;
//        $this->indexName = $indexName;
//        $this->indexType = $indexType;
        if (is_array($this->config['hosts'])) {
            $this->client = ClientBuilder::create()->setHosts($this->config['hosts'])->build();
        } else {
            $this->client = ClientBuilder::create()->build();
        }
    }

    /**
     * @return \Elasticsearch\Client
     * @Author: xiedf
     */
    public function getClient(): \Elasticsearch\Client
    {
        if ($this->client) {
            return $this->client;
        }
        return ClientBuilder::create()->setHosts($this->config['hosts'])->build();
    }

    /**
     * 搜索
     * @return Search
     * @Author: xiedf
     */
    public function search(SetIndex $setIndex)
    {
        return new Search($this->client, $setIndex);
    }

    public function CUD(SetIndex $setIndex)
    {
        return new CUD($this->client, $setIndex);
    }

    public function Setting(SetIndex $setIndex)
    {
        return new Setting($this->client, $setIndex);
    }

    public function Mapping(SetIndex $setIndex)
    {
        return new Mapping($this->client, $setIndex);
    }
}