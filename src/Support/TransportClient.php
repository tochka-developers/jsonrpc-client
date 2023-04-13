<?php

namespace Tochka\JsonRpcClient\Support;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tochka\JsonRpcClient\Contracts\TransportClientInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;

class TransportClient implements TransportClientInterface
{
    private const METHOD = 'POST';

    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function makeRequest(string $uri): RequestInterface
    {
        $request = $this->requestFactory->createRequest(self::METHOD, $uri);
        return $request->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function send(RequestInterface $request, JsonRpcRequestCollection $requestCollection): ?ResponseInterface
    {
        $requests = $requestCollection->toArray();

        if (count($requests) === 0) {
            return null;
        }

        if (count($requests) === 1) {
            $requests = $requests[0];
        }

        $body = json_encode($requests, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $request = $request->withBody(
            $this->streamFactory->createStream($body)
        );

        return $this->client->sendRequest($request);
    }
}
