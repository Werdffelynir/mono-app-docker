<?php

return [
    'debug' => true,
    'url' => getenv('APP_URL') . ':' . getenv('APP_PORT'),
    'database' => [
        'local' => [
            'driver' => getenv('DATABASE_DRIVER'),
            'path' => dirname(__DIR__) . getenv('DATABASE_PATH'),
        ],
    ]
];
