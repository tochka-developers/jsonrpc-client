<?php

namespace Tochka\JsonRpcClient\Exceptions;

use Throwable;

class ResponseException extends JsonRpcClientException
{
    public const CODE_PARSE_ERROR = -32700;
    public const CODE_INVALID_REQUEST = -32600;
    public const CODE_METHOD_NOT_FOUND = -32601;
    public const CODE_INVALID_PARAMS = -32602;
    public const CODE_INTERNAL_ERROR = -32603;
    public const CODE_INVALID_PARAMETERS = 6000;
    public const CODE_VALIDATION_ERROR = 6001;
    public const CODE_UNAUTHORIZED = 7000;
    public const CODE_FORBIDDEN = 7001;
    public const CODE_EXTERNAL_INTEGRATION_ERROR = 8000;
    public const CODE_INTERNAL_INTEGRATION_ERROR = 8001;

    public const MESSAGES = [
        self::CODE_PARSE_ERROR                => 'Ошибка обработки запроса',
        self::CODE_INVALID_REQUEST            => 'Неверный запрос',
        self::CODE_METHOD_NOT_FOUND           => 'Указанный метод не найден',
        self::CODE_INVALID_PARAMS             => 'Неверные параметры',
        self::CODE_INTERNAL_ERROR             => 'Внутренняя ошибка',
        self::CODE_INVALID_PARAMETERS         => 'Неверные параметры',
        self::CODE_VALIDATION_ERROR           => 'Ошибка валидации',
        self::CODE_UNAUTHORIZED               => 'Неверный ключ авторизации',
        self::CODE_FORBIDDEN                  => 'Доступ запрещен',
        self::CODE_EXTERNAL_INTEGRATION_ERROR => 'Ошибка внешних сервисов',
        self::CODE_INTERNAL_INTEGRATION_ERROR => 'Ошибка внутренних сервисов',
    ];

    protected $data;

    public function __construct($responseError, Throwable $previous = null)
    {
        $code = $responseError->code ?? 0;
        $message = $responseError->message ?? self::MESSAGES[$code] ?? 'Unknown error. Code ' . $code;
        $this->data = $responseError->data ?? null;

        parent::__construct($code, $message, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}