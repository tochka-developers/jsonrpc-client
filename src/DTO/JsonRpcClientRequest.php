<?php

namespace Tochka\JsonRpcClient\DTO;

use Tochka\JsonRpc\Standard\DTO\JsonRpcRequest;
use Tochka\JsonRpc\Standard\DTO\JsonRpcResponse;

class JsonRpcClientRequest
{
    private mixed $result = null;
    private JsonRpcRequest $request;
    private ?JsonRpcResponse $response = null;
    private array $additionalData = [];

    public function __construct(JsonRpcRequest $request)
    {
        $this->request = $request;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }

    public function getResponse(): ?JsonRpcResponse
    {
        return $this->response;
    }

    public function setResponse(?JsonRpcResponse $response): void
    {
        $this->response = $response;
    }

    public function getRequest(): JsonRpcRequest
    {
        return $this->request;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getAdditional(string $name, mixed $default = null): mixed
    {
        return $this->additionalData[$name] ?? $default;
    }

    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    public function setAdditional(string $name, mixed $value): void
    {
        $this->additionalData[$name] = $value;
    }
}
