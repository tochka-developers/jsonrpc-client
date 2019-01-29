<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Config;
use Tochka\JsonRpcClient\Contracts\Middleware;
use Tochka\JsonRpcClient\HttpClient;

class AuthTokenMiddleware implements Middleware
{
    protected $name;
    protected $value;

    public function __construct($options)
    {
        $this->name = $options['name'] ?? 'X-Access-Key';
        $this->value = $options['value'] ?? '';
    }

    public function handle(HttpClient $client, Config $config): void
    {
        $client->setHeader($this->name, $this->value);
    }
}