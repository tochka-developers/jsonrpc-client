<?php

namespace Tochka\JsonRpcClient\Standard;

use Illuminate\Contracts\Support\Arrayable;

class JsonRpcRequest implements Arrayable
{
    public $method;
    public $params;
    public $id;

    /**
     * JsonRpcRequest constructor.
     *
     * @param string     $method
     * @param array      $params
     * @param string|int $id
     *
     * @codeCoverageIgnore
     */
    public function __construct(string $method, array $params, $id = null)
    {
        $this->method = $method;
        $this->params = $params;
        $this->id = $id;
    }

    public function toArray(): array
    {
        $result = [
            'jsonrpc' => '2.0',
            'method'  => $this->method,
            'params'  => $this->params,
        ];

        if ($this->id !== null) {
            $result['id'] = $this->id;
        }

        return $result;
    }
}