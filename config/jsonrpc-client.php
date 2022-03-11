<?php

return [
    // Соединение по умолчанию
    'default' => 'api',
    
    'http' => [
        'client' => '\\GuzzleHttp\\Client',
        'requestFactory' => '\\Nyholm\\Psr7\\Factory\\Psr17Factory',
        'streamFactory' => '\\Nyholm\\Psr7\\Factory\\Psr17Factory',
    ],
    
    // Список соединений
    'connections' => [
        // Наименование соединения
        'api' => [
            // URL-адрес JsonRpc-сервера
            'url' => 'https://api.jsonrpc.com/v1/jsonrpc',
            
            // URL-адрес OpenRpc схемы
            'openRpcEndpoint' => 'https://api.jsonrpc.com/openrpc.json',
            
            // Имя прокси-класса для данного соединения
            'clientClass' => '\\App\\Api\\Client',
            
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
                
//                \Tochka\JsonRpcClient\Middleware\AdditionalHeadersMiddleware::class => [
//                    'headers' => [
//                        'X-Some-Header' => 'value'
//                    ]
//                ],

//                \Tochka\JsonRpcClient\Middleware\AuthBasicMiddleware::class => [
//                    'scheme'   => 'safe',
//                    'username' => 'username',
//                    'password' => 'password',
//                ],
            
            
            ],
        ],
    ],
];
