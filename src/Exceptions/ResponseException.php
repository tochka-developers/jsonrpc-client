<?php

namespace Tochka\JsonRpcClient\Exceptions;

use Throwable;
use Tochka\JsonRpcClient\Standard\JsonRpcError;

class ResponseException extends JsonRpcClientException
{
    protected $data;

    public function __construct(JsonRpcError $responseError, Throwable $previous = null)
    {
        $code = $responseError->code ?? 0;
        $message = $responseError->message ?? JsonRpcError::MESSAGES[$code] ?? 'Unknown error. Code ' . $code;
        $this->data = $responseError->data ?? null;

        parent::__construct($code, $message, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}