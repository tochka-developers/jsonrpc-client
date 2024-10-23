<?php

namespace Tochka\JsonRpcClient;

use Tochka\JsonRpcClient\Contracts\MiddlewareRegistryInterface;
use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;

class MiddlewareRegistry implements MiddlewareRegistryInterface
{
    private array $middleware = [];

    private array $onceExecutedMiddleware = [];

    public function setMiddleware(string $clientName, array $middleware, array $onceExecutedMiddleware): void
    {
        $this->middleware[$clientName] = $middleware;
        $this->onceExecutedMiddleware[$clientName] = $onceExecutedMiddleware;
    }

    public function append(array $middleware, ?string $clientName = null): void
    {
        if ($clientName !== null) {
            if ($this->isOnceExecuted($middleware)) {
                $this->onceExecutedMiddleware[$clientName][] = $middleware;
            } else {
                $this->middleware[$clientName][] = $middleware;
            }
        } else {
            foreach ($this->middleware as $clientName => $_) {
                $this->append($middleware, $clientName);
            }
        }
    }

    public function prepend(array $middleware, ?string $clientName = null): void
    {
        if ($clientName !== null) {
            if ($this->isOnceExecuted($middleware)) {
                array_unshift($this->onceExecutedMiddleware[$clientName], $middleware);
            } else {
                array_unshift($this->middleware[$clientName], $middleware);
            }
        } else {
            foreach ($this->middleware as $clientName => $_) {
                $this->prepend($middleware, $clientName);
            }
        }
    }

    public function getMiddleware(string $clientName): array
    {
        return $this->middleware[$clientName] ?? [];
    }

    public function getOnceExecutedMiddleware(string $clientName): array
    {
        return $this->onceExecutedMiddleware[$clientName] ?? [];
    }

    private function isOnceExecuted(array $middleware): bool
    {
        $implements = class_implements($middleware[0]);

        return $implements && \in_array(OnceExecutedMiddleware::class, $implements, true);
    }
}
