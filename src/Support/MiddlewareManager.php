<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;

class MiddlewareManager
{
    private array $fullRequestMiddleware = [];
    private array $fullRequestGlobalMiddleware = [];
    private array $jsonRpcRequestMiddleware = [];
    private array $jsonRpcRequestGlobalMiddleware = [];
    
    public function parseMiddleware(string $serverName, array $middlewareConfiguration): void
    {
        $middleware = $this->parseMiddlewareConfiguration($middlewareConfiguration);
        $this->sortMiddleware($middleware, $serverName);
    }
    
    public function getMiddlewareForFullRequest(string $serverName): array
    {
        return array_merge($this->fullRequestGlobalMiddleware, $this->fullRequestMiddleware[$serverName] ?? []);
    }
    
    public function getMiddlewareForJsonRpcRequest(string $serverName): array
    {
        return array_merge($this->jsonRpcRequestGlobalMiddleware, $this->jsonRpcRequestMiddleware[$serverName] ?? []);
    }
    
    public function prependMiddleware($middleware, ?string $serverName = null): void
    {
        $middlewareList = &$this->getCurrentMiddlewareList($this->isFullRequestMiddleware($middleware), $serverName);
    
        if (in_array($middleware, $middlewareList, true) === false) {
            array_unshift($middlewareList, $middleware);
        }
    }
    
    public function appendMiddleware($middleware, ?string $serverName = null): void
    {
        $middlewareList = &$this->getCurrentMiddlewareList($this->isFullRequestMiddleware($middleware), $serverName);
    
        if (in_array($middleware, $middlewareList, true) === false) {
            $middlewareList[] = $middleware;
        }
    }
    
    private function &getCurrentMiddlewareList(bool $isFullRequestMiddleware, ?string $serverName = null): array
    {
        if ($serverName === null) {
            if ($isFullRequestMiddleware) {
                return $this->fullRequestGlobalMiddleware;
            }
            
            return $this->jsonRpcRequestGlobalMiddleware;
        }
    
        if ($isFullRequestMiddleware) {
            return $this->fullRequestMiddleware[$serverName];
        }
        
        return $this->jsonRpcRequestMiddleware[$serverName];
    }
    
    private function parseMiddlewareConfiguration($middleware): array
    {
        $result = [];
        foreach ($middleware as $name => $m) {
            if (is_array($m)) {
                $result[] = [$name, $m];
            } else {
                $result[] = [$m, []];
            }
        }
        
        return $result;
    }
    
    private function sortMiddleware(array $middleware, ?string $serverName = null): void
    {
        foreach ($middleware as $m) {
            if ($this->isFullRequestMiddleware($m[0])) {
                if ($serverName !== null) {
                    $this->fullRequestMiddleware[$serverName][] = $m;
                } else {
                    $this->fullRequestGlobalMiddleware[] = $m;
                }
            } elseif ($serverName !== null) {
                $this->jsonRpcRequestMiddleware[$serverName][] = $m;
            } else {
                $this->jsonRpcRequestGlobalMiddleware[] = $m;
            }
        }
    }
    
    private function isFullRequestMiddleware($middleware): bool
    {
        $implements = class_implements($middleware);
        return $implements && in_array(OnceExecutedMiddleware::class, $implements, true);
    }
}
