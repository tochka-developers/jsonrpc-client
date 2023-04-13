<?php

namespace Tochka\JsonRpcClient\DTO;

use Psr\Http\Message\RequestInterface;

class JsonRpcRequestContainer
{
    private JsonRpcRequestCollection $jsonRpcRequests;
    private RequestInterface $request;

    public function __construct(RequestInterface $request, JsonRpcRequestCollection $jsonRpcRequests)
    {
        $this->request = $request;
        $this->jsonRpcRequests = $jsonRpcRequests;
    }

    public function getJsonRpcRequests(): JsonRpcRequestCollection
    {
        return $this->jsonRpcRequests;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

}
