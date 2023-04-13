<?php

namespace Tochka\JsonRpcClient\Contracts;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;

interface TransportClientInterface
{
    public function makeRequest(string $uri): RequestInterface;

    /**
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function send(RequestInterface $request, JsonRpcRequestCollection $requestCollection): ?ResponseInterface;
}
