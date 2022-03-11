<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpcClient\Exceptions\ResponseException;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;

class Request
{
    private JsonRpcRequest $jsonRpcRequest;
    private JsonRpcResponse $jsonRpcResponse;
    private Result $result;
    private array $additional = [];
    private string $clientName;

    public function __construct(JsonRpcRequest $request, string $clientName)
    {
        $this->jsonRpcRequest = $request;
        $this->result = new Result();
        $this->clientName = $clientName;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setAdditional(array $values): void
    {
        $this->additional = $values;
    }

    public function getAdditional($key, $default = null)
    {
        return $this->additional[$key] ?? $default;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getId(): string
    {
        return $this->jsonRpcRequest->getId();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJsonRpcRequest(): JsonRpcRequest
    {
        return $this->jsonRpcRequest;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setJsonRpcRequest(JsonRpcRequest $request): void
    {
        $this->jsonRpcRequest = $request;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJsonRpcResponse(): JsonRpcResponse
    {
        return $this->jsonRpcResponse;
    }

    /**
     * @throws ResponseException
     */
    public function setJsonRpcResponse(JsonRpcResponse $response): void
    {
        $this->jsonRpcResponse = $response;

        $this->parseResult();
    }

    /**
     * @throws ResponseException
     */
    protected function parseResult(): void
    {
        if (!empty($this->jsonRpcResponse->error)) {
            throw new ResponseException($this->jsonRpcResponse->error);
        }

        $this->result->setResult($this->jsonRpcResponse->result);
    }
    
    public function getClientName(): string
    {
        return $this->clientName;
    }
}
