<?php

namespace Tochka\JsonRpcClient\Support;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class PsrHttpClient
{
    private const METHOD = 'POST';
    
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }
    
    public function createHttpRequest(string $url): RequestInterface
    {
        return $this->requestFactory->createRequest(self::METHOD, $url)
            ->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * @throws \JsonException
     * @throws ClientExceptionInterface
     */
    public function send(RequestInterface $httpRequest, array $jsonRpcRequestCollection)
    {
        if (count($jsonRpcRequestCollection) === 1) {
            $body = json_encode($jsonRpcRequestCollection[0], JSON_THROW_ON_ERROR);
        } else {
            $body = json_encode($jsonRpcRequestCollection, JSON_THROW_ON_ERROR);
        }
    
        $httpRequest = $httpRequest->withBody(
            $this->streamFactory->createStream($body)
        );
        
        $httpResponse = $this->client->sendRequest($httpRequest);
        
        return json_decode($httpResponse->getBody(), false, 512, JSON_THROW_ON_ERROR);
    }
}
