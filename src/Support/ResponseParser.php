<?php

namespace Tochka\JsonRpcClient\Support;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpc\Standard\DTO\JsonRpcResponse;
use Tochka\JsonRpc\Standard\Exceptions\InvalidResponseException;
use Tochka\JsonRpcClient\Contracts\ResponseParserInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcResponseCollection;
use Tochka\JsonRpcClient\Exceptions\Errors\HttpIncorrectStatusCodeError;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientHttpErrorException;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientResponseParseException;

class ResponseParser implements ResponseParserInterface
{
    public function parse(string $connectionName, ResponseInterface $response): JsonRpcResponseCollection
    {
        $collection = new JsonRpcResponseCollection();

        if ($response->getStatusCode() !== 200) {
            throw new JsonRpcClientHttpErrorException(
                connectionName: $connectionName,
                data:           new HttpIncorrectStatusCodeError(
                                    $response->getStatusCode(),
                                    $response->getReasonPhrase()
                                )
            );
        }

        try {
            $parsedResponses = json_decode((string)$response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new JsonRpcClientResponseParseException(
                connectionName: $connectionName,
                data:           new HttpIncorrectStatusCodeError(
                                    $response->getStatusCode(),
                                    $response->getReasonPhrase()
                                )
            );
        }

        if (is_object($parsedResponses)) {
            $parsedResponses = [$parsedResponses];
        }

        $parseErrors = [];
        foreach ($parsedResponses as $parsedResponse) {
            try {
                $jsonRpcResponse = JsonRpcResponse::from($parsedResponse);
                $collection->add($jsonRpcResponse);
            } catch (InvalidResponseException $e) {
                $parseErrors[] = $e;
            }
        }

        if (!empty($parseErrors)) {
            throw new JsonRpcClientResponseParseException(
                connectionName: $connectionName,
                data:           new HttpIncorrectStatusCodeError(
                                    $response->getStatusCode(),
                                    $response->getReasonPhrase()
                                )
            );
        }

        return $collection;
    }
}
