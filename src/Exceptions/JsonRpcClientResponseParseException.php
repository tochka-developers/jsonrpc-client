<?php

namespace Tochka\JsonRpcClient\Exceptions;

class JsonRpcClientResponseParseException extends JsonRpcClientException
{
    public function __construct(
        string $connectionName,
        ?string $message = null,
        ?object $data = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($connectionName, self::CODE_RESPONSE_PARSE_ERROR, $message, $data, $previous);
    }
}
