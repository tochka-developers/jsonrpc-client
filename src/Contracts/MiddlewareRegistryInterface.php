<?php

namespace Tochka\JsonRpcClient\Contracts;

interface MiddlewareRegistryInterface
{
    public function setMiddleware(string $clientName, array $middleware, array $onceExecutedMiddleware): void;
    
    public function append(array $middleware, ?string $clientName = null): void;
    
    public function prepend(array $middleware, ?string $clientName = null): void;
    
    public function getMiddleware(string $clientName): array;
    
    public function getOnceExecutedMiddleware(string $clientName): array;
}
