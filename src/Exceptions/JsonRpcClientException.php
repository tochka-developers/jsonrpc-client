<?php

namespace Tochka\JsonRpcClient\Exceptions;

use Throwable;

class JsonRpcClientException extends \Exception
{
    public const CODE_RESPONSE_PARSE_ERROR = 1000;
    public const CODE_HTTP_REQUEST_ERROR = 2000;
    public const CODE_UNKNOWN_REQUEST_ERROR = 3000;


    private const MESSAGES = [
        self::CODE_RESPONSE_PARSE_ERROR  => 'Error parsing response from Api. An error has occurred on the server',
        self::CODE_HTTP_REQUEST_ERROR    => 'Request http error',
        self::CODE_UNKNOWN_REQUEST_ERROR => 'Request unknown error',
    ];

    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if ($message === null) {
            $message = self::MESSAGES[$code] ?? 'Unknown error. Code ' . $code;
        }

        parent::__construct($message, $code, $previous);
    }
}