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
        $this->result = new Result();
    }

    /**
     * @param array $values
     *
     * @codeCoverageIgnore
     */
    public function setAdditional(array $values): void
    {
        $this->additional = $values;
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function getAdditional($key, $default = null)
    {
        return $this->additional[$key] ?? $default;
    }

    /**
     * @return Result
     * @codeCoverageIgnore
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getId(): string
    {
        return $this->jsonRpcRequest->id;
    }

    /**
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcRequest
     * @codeCoverageIgnore
     */
    public function getJsonRpcRequest(): JsonRpcRequest
    {
        return $this->jsonRpcRequest;
    }

    /**
     * @param \Tochka\JsonRpcClient\Standard\JsonRpcRequest $request
     *
     * @codeCoverageIgnore
     */
    public function setJsonRpcRequest(JsonRpcRequest $request): void
    {
        $this->jsonRpcRequest = $request;
    }

    /**
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcResponse
     * @codeCoverageIgnore
     */
    public function getJsonRpcResponse(): JsonRpcResponse
    {
        return $this->jsonRpcResponse;
    }

    /**
     * @param \Tochka\JsonRpcClient\Standard\JsonRpcResponse $response
     *
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
        if (!empty($this->jsonRpcResponse->error)) {
            throw new ResponseException($this->jsonRpcResponse->error);
        }

        $this->result->setResult($this->jsonRpcResponse->result);
    }
}