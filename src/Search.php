<?php

namespace zzfufu\ZzElastic;

class Search
{
    protected $client = null;

//    protected $indexName = '';
//    protected $indexType = '';

    protected SetIndex $setIndex;

    public function __construct(\Elasticsearch\Client $connection, SetIndex $setIndex)
    {
        $this->client = $connection;
//        $this->indexName = $setIndex->getIndexName();
//        $this->indexType = $setIndex->getIndexType();
        $this->setIndex = $setIndex;
    }

    /**
     * 初始化索引参数
     * @author joniding
     * @return array
     */
    public function initParams()
    {
//        return [
//            'index' => $this->setIndex->getIndexName(),
//            'type'  => $this->setIndex->getIndexType(),
//        ];
        return $this->setIndex->toArray();
    }

    /**
     * 单字段模糊查询
     * 满足单个字段查询（不带分页+排序）match 分词查询
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function search($data = [])
    {
        try{
            $params = $this->initParams();

            if (!empty($data)){
                $field = key($data);
                $query = [
                    'match' => [
                        $field => [
                            'query' => $data[$field],
                            'minimum_should_match'  => '90%'  //相似度，匹配度
                        ]
                    ]
                ];
                $params['body']['query'] = $query;
            }
            $res = $this->client->search($params);

        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }

    /**
     * 根据唯一id查询数据
     * @author joniding
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function searchById($id)
    {
        try{
            $params = $this->initParams();
            $params['id'] = $id;

            $res = $this->client->get($params);
        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }

    /**
     * 根据关键字查询数据
     * 多个字段查询：multi_match
     * @author joniding
     * @param $data
     * $data['condition'] 条件组合
     * $data['es_size'] 每页显示数量
     * $data['es_from'] 从第几条开始
     * $data['es_sort_field'] 自定义排序字段
     * @return array|bool
     * @throws \Exception
     */
    public function searchMulti($data = [])
    {
        try{

            if (!is_array($data)){
                return [];
            }
            $params = $this->initParams();
            if (array_key_exists('fields',$data)){
                $params['_source'] = $data['fields'];
            }

            //分页
            if (array_key_exists('page_size',$data)){
                $params['size'] = !empty($data['page_size'])?$data['page_size']:1;
                //前端页码默认传1
                $params['from'] = !empty($data['page'])?($data['page']-1)*$params['size']:0;
                unset($data['page_size'],$data['page']);
            }
            //排序
            if (array_key_exists('sort_field',$data)){
                $sort_file = !empty($data['sort_field'])?$data['sort_field']:'total_favorited';
                $sort_rule = !empty($data['sort_rule'])?$data['sort_rule']:'desc';
                $params['body']['sort'][] = [
                    ''.$sort_file.'' => [
                        'order' => ''.$sort_rule.'',
                    ]
                ];
                unset($data['sort_field'],$data['sort_rule']);
            }else{
//                $params['body']['sort'][] = [
//                    'created_at' => [
//                        'order' => 'desc',
//                    ]
//                ];
            }
            /**
             * 深度（滚动）分页
             */
            if (array_key_exists('scroll',$data)){
                $params['scroll'] = $data['scroll'];
            }

            //条件组合
            if (array_key_exists('condition',$data)){
                $query     = [];
                $condition = $data['condition'];

                /**
                 * 组合查询
                 */
                if (array_key_exists('bool',$condition)){
                    //必须满足
                    if (array_key_exists('must',$condition['bool'])){
                        foreach ($condition['bool']['must'] as $key => $val){
                            if (is_array($val)){
                                $query['bool']['must'][]['range'] = [
                                    $key => [
                                        'gte'  => $val[0],
                                        'lte'    => $val[1]
                                    ]
                                ];
                            }else{
                                $query['bool']['must'][]['match'] = [
                                    $key => $val
                                ];
                            }
                        }
                    }
                }
                !empty($query) && $params['body']['query'] = $query;
            }
            $res = $this->client->search($params);
        }catch (\Exception $e){
            throw $e;
        }

        return $res;
    }

    /**
     * 聚合统计,方差
     * MySQL 中的 group by、avg、sum
     * @param $data
     * @return array
     * @throws \Exception
     * @author:joniding
     * @date:Times
     */
    public function searchAggs($data)
    {
        try{
            if (!is_array($data)){
                return [];
            }
            $query= [];
            $params = $this->initParams();
            $params['size'] = 0;

            /**
             * 条件组合过滤，筛选条件
             */
            if (array_key_exists('condition',$data)){
                $condition = $data['condition'];
                if (array_key_exists('bool',$condition)){
                    //必须满足
                    if (array_key_exists('must',$condition['bool'])){
                        foreach ($condition['bool']['must'] as $key => $val){
                            if (is_array($val)){
                                $query['bool']['must'][]['range'] = [
                                    $key => [
                                        'gte' => $val[0],
                                        'lte' => $val[1]
                                    ]
                                ];
                            }else{
                                $query['bool']['must'][]['match'] = [
                                    $key => $val
                                ];
                            }
                        }
                        $params['body']['query'] = $query;
                    }
                }
            }

            //分组、排序设置
            if (array_key_exists('agg',$data)){
                $agg = [];
                //字段值
                if (array_key_exists('terms',$data['agg'])){
                    $agg['_result']['terms'] = [
                        'field'   => $data['agg']['terms'],
                        'size'    => 500,
                    ];
                    if (array_key_exists('order',$data['agg'])){
                        foreach ($data['agg']['order'] as $key => $val){
                            $fields = 'result.'.$key;
                            $agg['_result']['terms']['order'] = [
                                $fields => $val
                            ];
                            unset($fields);
                        }
                    }
                }
                //统计
                if (array_key_exists('field',$data['agg'])){
                    $agg['_result']['aggs'] = [
                        'result' => [
                            'extended_stats' => [
                                'field'  => $data['agg']['field']
                            ]
                        ]
                    ];
                }

                //日期聚合统计
                if (array_key_exists('date',$data['agg'])){
                    $date_agg = $data['agg']['date'];
                    //根据日期分组
                    if (array_key_exists('field',$date_agg)){
                        $agg['result'] = [
                            'date_histogram' => [
                                'field'     => $data['agg']['date']['field'],
                                'interval'  => '2h',
                                'format'    => 'yyyy-MM-dd  HH:mm:ss'
                            ]
                        ];
                    }

                    if (array_key_exists('agg',$date_agg)){
                        //分组

                        if (array_key_exists('terms',$date_agg['agg'])){
                            $agg['result']['aggs']['result']['terms'] = [
                                'field' => $date_agg['agg']['terms'],
                                'size'  => 100,
                            ];
                        }
                        //统计最大、最小值等
                        if (array_key_exists('stats',$date_agg['agg'])){
                            $agg['result']['aggs']['result']['aggs'] = [
                                'result_stats' => [
                                    'extended_stats' => [
                                        'field' => $date_agg['agg']['stats']
                                    ]
                                ]
                            ];
                        }
                    }

                }
                $params['body']['aggs'] = $agg;
            }
//            \Log::info(json_encode($params));
            $res = $this->client->search($params);

        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }

    /**
     * 批量查询，只能根据id来查
     * @param $data
     * @return array
     * @throws \Exception
     * @author:joniding
     * @date:2019/8/5 19:51
     */
    public function mGet($data)
    {
        try{
            if (!is_array($data)) return [];
            //初始化索引
            $params = $this->initParams();

            if (array_key_exists('fields',$data)){
                $query['ids'] = $data['fields'];
                $params['body'] = $query;
            }
            $res = $this->client->mget($params);
            return $res;

        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 深度分页
     * @param $data
     * @return array
     * @throws \Exception
     * @date:2019/8/16 14:49
     */
    public function scroll($data)
    {
        try{
            $params = [
                'scroll_id' => $data['scroll_id'],
                'scroll'    => '1m'
            ];

            $res = $this->client->scroll($params);
//            \Log::info(json_encode($params));

            if (isset($res['_scroll_id']) && $res['_scroll_id'] != $data['scroll_id']){
                $this->client->clearScroll(['scroll_id' => $data['scroll_id'] ]);
            }

            return $res;
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * 查询索引是否存在
     * @return array|bool
     * @throws \Exception
     */
    public function exist()
    {
        try{
            $params['index'] = $this->index_name;

            $res = $this->client->indices()->exists($params);

        }catch (\Exception $e){
            throw $e;
        }
        return $res;
    }
}