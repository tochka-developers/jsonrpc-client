<?php

namespace Tochka\JsonRpcClient\Middleware;

use GuzzleHttp\RequestOptions;
use Tochka\JsonRpcClient\Config;
use Tochka\JsonRpcClient\Contracts\Middleware;
use Tochka\JsonRpcClient\HttpClient;

class AuthBasicMiddleware implements Middleware
{
    protected $scheme;
    protected $username;
    protected $password;

    public function __construct($options)
    {
        $this->scheme = $options['scheme'] ?? 'basic';
        $this->username = $options['username'] ?? '';
        $this->password = $options['password'] ?? '';
    }

    public function handle(HttpClient $client, Config $config): void
    {
        $client->setOption(RequestOptions::AUTH, [
            $this->username,
            $this->password,
            $this->scheme,
        ]);
    }
}