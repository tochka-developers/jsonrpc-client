<?php

namespace Tochka\JsonRpcClient;

use Tochka\JsonRpcClient\Exceptions\ResponseException;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;

class Request
{
    /** @var \Tochka\JsonRpcClient\Standard\JsonRpcRequest */
    protected $jsonRpcRequest;

    /** @var \Tochka\JsonRpcClient\Standard\JsonRpcResponse */
    protected $jsonRpcResponse;

    protected $result;

    protected $additional = [];

    public function __construct(JsonRpcRequest $request)
    {
        $this->jsonRpcRequest = $request;
        $this->result = new Result;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setAdditional(array $values): void
    {
        $this->additional = $values;
    }

    /**
     * @param  null  $default
     * @return mixed|null
     */
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
        return $this->jsonRpcRequest->id;
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
     * @throws \Tochka\JsonRpcClient\Exceptions\ResponseException
     */
    public function setJsonRpcResponse(JsonRpcResponse $response): void
    {
        $this->jsonRpcResponse = $response;
        $this->parseResult();
    }

    /**
     * @throws \Tochka\JsonRpcClient\Exceptions\ResponseException
     */
    protected function parseResult(): void
    {
        if (! empty($this->jsonRpcResponse->error)) {
            throw new ResponseException($this->jsonRpcResponse->error);
        }

        $this->result->setResult($this->jsonRpcResponse->result);
    }
}
