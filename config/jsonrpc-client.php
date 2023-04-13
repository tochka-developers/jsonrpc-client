<?php

return [
    // Имя клиента. Используется в качестве префикса к ID запросов
    'clientName' => 'default',

    // Список соединений
    'connections' => [
        // Наименование соединения
        'api' => [
            // URL-адрес JsonRpc-сервера
            'url' => 'https://api.jsonrpc.com/v1/jsonrpc',

            // URL-адрес openrpc-схемы сервера
            'openrpc' => 'https://api.jsonrpc.com/v1/openrpc.json',

            // Имя прокси-класса для данного соединения
            'clientClass' => '\\App\\Api\\Client',

            // Передача параметров по имени
            'parametersByName' => true,

            // Список middleware
            'middleware' => [
//                \Tochka\JsonRpcClient\Middleware\AdditionalHeadersMiddleware::class => [
//                    'headerName1' => 'headerValue',
//                    'headerName2' => ['value1', 'value2',], // To include multiple headers with the same name
//                ],

                \Tochka\JsonRpcClient\Middleware\AuthTokenMiddleware::class => [
                    'name' => 'X-Access-Key',
                    'value' => 'TokenValue',
                ],

                \Tochka\JsonRpcClient\Middleware\AuthBasicMiddleware::class => [
                    'scheme' => 'safe',
                    'username' => 'username',
                    'password' => 'password',
                ],
            ],
        ],
    ],
];
