<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\JsonRpcClient;
use Tochka\JsonRpcClient\Route\DTO\Route;

interface RouterInterface
{
    public function getRoute(JsonRpcClient $client, string $method): ?Route;
}
