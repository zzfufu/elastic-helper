<?php
require_once __DIR__.'/../vendor/autoload.php';

$config = [
    'connections' => [
        '127.0.0.1:9200',
        '127.0.0.1:9201',
        '127.0.0.1:9202',
    ]
];
$client = new \Elastica\Client();

$index = $client->getIndex('test');
$index->create(array(), true);
//$type = $index->getType('test');
$index->addDocument(new \Elastica\Document(1, array('username' => 'ruflin')));
$index->refresh();

$query = '{"query":{"query_string":{"query":"ruflin"}}}';

$path = $index->getName() . '/' . $index->getName() . '/_search';

$response = $client->request($path, \Elastica\Request::GET, $query);
$responseArray = $response->getData();
var_dump($responseArray);