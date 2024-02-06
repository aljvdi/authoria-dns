<?php
require __DIR__ . '/vendor/autoload.php';

$router = new \Bramus\Router\Router();
$router->setNamespace('\Javadi\Authoria\DNS\controllers');
$router->before('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', '/.*', 'APIController@init');
$router->set404('APIController@notFound');

$router->get('/', function() {
    echo json_encode(['up'=> true, 'version' => '1.0.0', 'time' => time()]);
});

$router->mount('/api', function() use ($router) {
    $router->mount('/v1', function() use ($router) {
        $router->post('/new', 'APIController@newRequest');
        $router->get('/verify', 'APIController@verifyRequest');
        $router->post('/bulk-verify', 'APIController@bulkVerifyRequest');
    });
});

$router->run();