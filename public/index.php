<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Lib\Core\Config;
use Lib\Core\Controller;
use Lib\Core\Core;
use Lib\Core\DB;
use Lib\Core\Render;
use Lib\Core\Request;
use Lib\Core\Router;

Core::configure(Config::class, [
    'files' => [
        Config::DIRECTIVE => dirname(__DIR__) . '/app/config.php'
    ],
    'set' => [
        'path_root' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
        'path_app' => dirname(__DIR__) . DIRECTORY_SEPARATOR .'app' . DIRECTORY_SEPARATOR,
        'path_resources' => dirname(__DIR__) . DIRECTORY_SEPARATOR .'resources' . DIRECTORY_SEPARATOR,
    ],
]);

Core::container(DB::class, [
    'driver' => 'sqlite',
    'path' => Config::get('path_resources') . 'database/database.sqlite',
]);

Core::configure(Render::class, [
    'path' => Config::get('path_resources') . 'views/',
    'layout' => 'layout.php',
    'vars' => [
        'title' => 'Application',
    ],
]);

Core::configure(Router::class, [
    'resources_path' => './public',
    'resources_rewrite' => false,
    'routes_path' => Config::get('path_app') . 'routes.php',
    'autostart' => false,
]);

Core::container(Request::class);

Core::configure(Controller::class, [
    'db' => Core::container(DB::class),
    'render' => Core::container(Render::class),
    'request' => Core::container(Request::class),
]);
