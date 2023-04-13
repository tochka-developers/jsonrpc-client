<?php

namespace Tochka\JsonRpcClient\Exceptions;

class JsonRpcClientException extends \RuntimeException
{
    public const CODE_RESPONSE_PARSE_ERROR = 1000;
    public const CODE_HTTP_REQUEST_ERROR = 2000;
    public const CODE_UNKNOWN_REQUEST_ERROR = 3000;

    public const MESSAGE_RESPONSE_PARSE_ERROR = 'Error parsing response from API [%s]';
    public const MESSAGE_HTTP_REQUEST_ERROR = 'Request http error from API [%s]';
    public const MESSAGE_UNKNOWN_REQUEST_ERROR = 'Request unknown error from API [%s]';

    public const DEFAULT_MESSAGES = [
        self::CODE_RESPONSE_PARSE_ERROR => self::MESSAGE_RESPONSE_PARSE_ERROR,
        self::CODE_HTTP_REQUEST_ERROR => self::MESSAGE_HTTP_REQUEST_ERROR,
        self::CODE_UNKNOWN_REQUEST_ERROR => self::MESSAGE_UNKNOWN_REQUEST_ERROR,
    ];

    private array|object|null $data;
    private string $connectionName;

    public function __construct(
        string $connectionName,
        int $code,
        ?string $message = null,
        ?object $data = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(self::getDefaultMessage($connectionName, $code, $message), $code, $previous);

        $this->data = $data;
        $this->connectionName = $connectionName;
    }

    public function getData(): ?object
    {
        return $this->data;
    }

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    public static function getDefaultMessage(string $connectionName, int $code, ?string $message = null): string
    {
        return $message ?? sprintf(static::DEFAULT_MESSAGES[$code], $connectionName) ?? 'Unexpected error';
    }
}
