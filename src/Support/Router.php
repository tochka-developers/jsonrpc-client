<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpcClient\Contracts\RouterInterface;
use Tochka\JsonRpcClient\JsonRpcClient;
use Tochka\JsonRpcClient\Route\DTO\Route;

class Router implements RouterInterface
{
    public function getRoute(JsonRpcClient $client, string $method): ?Route
    {
        $realMethodName = $client->_getMethodName($method);
        $clientClassName = $client->_getClientConfig()->clientClass;
        [, $clientClassMethod] = explode('::', $method);

        $route = new Route($client->_getClientConfig()->connectionName, $clientClassName, $clientClassMethod, $realMethodName);

        $reflection = new \ReflectionClass($client);
        $reflectionMethod = $reflection->getMethod($clientClassMethod);

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {

        }

        $reflectionReturnType = $reflectionMethod->getReturnType();

        return $route;
    }
}
