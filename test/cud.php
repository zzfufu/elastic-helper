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
$set = $conn->Setting($setIndex);

$params = [
    'index' => $indexName,
    'body' => [
        'settings' => [
            'number_of_replicas' => 3,
            'refresh_interval' => -1
        ]
    ]
];
$set->putSettings($params);
$set->getSettings(['index' => $indexName]);