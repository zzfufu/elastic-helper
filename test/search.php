<?php
require_once __DIR__.'/../vendor/autoload.php';

$indexName = 'zzswoole_user';
$indexType = 'user';
$config = [
    'hosts' => [
        '127.0.0.1:9200',
//        '127.0.0.1:9201',
//        '127.0.0.1:9202',
    ]
];

$setIndex = new \zzfufu\ZzElastic\SetIndex();
$setIndex->setIndexName($indexName);
$setIndex->setIndexType($indexType);

$conn = new \zzfufu\ZzElastic\ElasticsearchConnection($config);
$search = $conn->search($setIndex);
//$res = $search->searchMulti();
$where = ['real_name' => '廖华春'];

$where = [
    'query' => [
        'bool' => [
            'must' => [
                [ 'match' => [ 'real_name' => '廖华春' ] ],
//                [ 'match' => [ 'user_name' => 'liaohuachun' ] ],
            ]
        ]
    ]
];

$where = [
    'query' => [
        'bool' => [
            'filter' => [
                'term' => [ 'sex' => '女' ]
            ],
            'should' => [
                'match' => [ 'real_name' => '廖华春' ]
            ]
        ]
    ]
];

//$res = $search->search($where);
//var_dump($res);

$search = $conn->getClient();

$params = [
    "scroll" => "30s",          // how long between scroll requests. should be small!
    "size" => 50,               // how many results *per shard* you want back
    "index" => $indexName,
    "body" => [
        "query" => [
            "match_all" => new \stdClass()
        ]
    ]
];

$response = $search->search($params);
while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
    $scroll_id = $response['_scroll_id'];

    // Execute a Scroll request and repeat
    $response = $client->scroll([
            "scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
            "scroll" => "30s"           // and the same timeout window
        ]
    );
}