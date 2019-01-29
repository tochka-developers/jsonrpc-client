<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\Config;
use Tochka\JsonRpcClient\HttpClient;

interface Middleware
{
    public function handle(HttpClient $client, Config $config): void;
}