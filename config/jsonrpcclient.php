<?php

return [
    // Имя клиента. Используется в качестве префикса к ID запросов
    'clientName' => 'prodam',

    // Соединение по умолчанию
    'default' => 'api',

    // Список соединений
    'connections' => [
        // Наименование соединения
        'api' => [
            // URL-адрес JsonRpc-сервера
            'url' => 'https://api.jsonrpc.com/v1/jsonrpc',
            // Аутентификация клиента
            'auth' => [
                // Аутентификация с помощью заголовка. Если не нужна - указывается значение null
                'headerToken' => null,
//                'headerToken' => [
//                    // Имя заголовка для авторизации
//                    'name' => 'X-Access-Key',

//                    // Значение заголовка для авторизации (токен)
//                    'key' => 'ToKeN12345'
//                ],
                // HTTP аутентификация. Если не нужна - указывается значение null
                'http' => null,
//                'http' => [
//                    // Схема аутентификации. Возможные варианты:
//                    // basic, digest, ntlm, gss, any (basic, digest, ntlm, gss), safe (digest, ntlm, gss)
//                    'scheme' => 'safe',
//                    'username' => 'username',
//                    'password' => 'password'
//                ]
            ],
            // Имя прокси-класса для данного соединения
            'clientClass' => '\\App\\Api\\Client',
        ]
    ]
];