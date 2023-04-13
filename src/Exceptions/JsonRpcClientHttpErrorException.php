<?php

namespace Tochka\JsonRpcClient\Exceptions;

class JsonRpcClientHttpErrorException extends JsonRpcClientException
{
    public function __construct(
        string $connectionName,
        ?string $message = null,
        ?object $data = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($connectionName, self::CODE_HTTP_REQUEST_ERROR, $message, $data, $previous);
    }
}
