<?php

namespace Tochka\JsonRpcClient\Exceptions\Errors;

use Illuminate\Contracts\Support\Arrayable;

class HttpIncorrectStatusCodeError implements Arrayable
{
    public const MESSAGE = 'Incorrect response code';

    private int $statusCode;
    private string $reason;

    public function __construct(int $statusCode, string $reason)
    {
        $this->statusCode = $statusCode;
        $this->reason = $reason;
    }

    public function toArray(): array
    {
        return [
            'message' => self::MESSAGE,
            'status_code' => $this->statusCode,
            'reason' => $this->reason
        ];
    }
}
