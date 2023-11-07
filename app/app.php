<?php

use Ketwaroo\MinetestServerList\App;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$app = new App($config);

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($app) {
    $r->addRoute('GET', '/', fn() => 'not implemented, might steal from original later.');
    $r->addRoute('GET', '/geoip', $app->handleGeoip(...));
    $r->addRoute('GET', '/list', $app->handleList(...));
    $r->addRoute(['GET', 'POST'], '/announce', $app->handleAnnounce(...));
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri        = empty($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'];

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        header('HTTP/404');

        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        header('HTTP/405');

        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        // nyeh..
        $vars    = array_merge($_REQUEST, $routeInfo[2]);

        $handler($vars);

        break;
}
