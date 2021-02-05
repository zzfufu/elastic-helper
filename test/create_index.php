<?php
require_once __DIR__.'/../vendor/autoload.php';

$indexName = 'zzswoole_user';
$indexType = 'user';
$config = [
    'hosts' => [
        '127.0.0.1:9200',
        '127.0.0.1:9201',
        '127.0.0.1:9202',
    ]
];

$setIndex = new \zzfufu\ZzElastic\SetIndex();
$setIndex->setIndexName($indexName);
$setIndex->setIndexType($indexType);

$conn = new \zzfufu\ZzElastic\ElasticsearchConnection($config);
$cud = $conn->CUD($setIndex);
//$cud->deleteIndex($indexName);

$params = [
    'index' => $indexName,
    'body' => [
        'settings' => [
            'number_of_shards' => 3,
            'number_of_replicas' => 2
        ],
        'mappings' => [
            $indexName => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'user_name' => [
                        'type' => 'string',
                        'analyzer' => 'standard'
                    ]
                ]
            ]
        ]
    ]
];
$res = $cud->createIndex($params['body']['settings'], $params['body']['mappings']);
//var_dump($res);
//$setting = $conn->Setting($setIndex);
//$res = $setting->getSettings(['index' => $indexName]);
var_dump($res);