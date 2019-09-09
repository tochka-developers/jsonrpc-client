<?php

namespace Tochka\JsonRpcClient\Standard;

class JsonRpcError
{
    public const CODE_PARSE_ERROR = -32700;
    public const CODE_INVALID_REQUEST = -32600;
    public const CODE_METHOD_NOT_FOUND = -32601;
    public const CODE_INVALID_PARAMS = -32602;
    public const CODE_INTERNAL_ERROR = -32603;

    public const MESSAGES = [
        self::CODE_PARSE_ERROR      => 'Parse error',
        self::CODE_INVALID_REQUEST  => 'Invalid Request',
        self::CODE_METHOD_NOT_FOUND => 'Method not found',
        self::CODE_INVALID_PARAMS   => 'Invalid params',
        self::CODE_INTERNAL_ERROR   => 'Internal error',
    ];

    public $code;
    public $message;
    public $data;

    public function __construct($data)
    {
        $this->code = $data->code;
        $this->message = $data->message;
        $this->data = $data->data ?? (object) [];
    }
}