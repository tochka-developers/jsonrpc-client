<?php

namespace Tochka\JsonRpcClient\Standard;

use Illuminate\Contracts\Support\Arrayable;

class JsonRpcRequest implements Arrayable
{
    private string $method;
    private array $params;
    private ?string $id;
    private string $jsonrpc = '2.0';
    
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $method, array $params, ?string $id = null)
    {
        $this->method = $method;
        $this->params = $params;
        $this->id = $id;
    }
    
    public function toArray(): array
    {
        $result = [
            'jsonrpc' => $this->jsonrpc,
            'method' => $this->method,
            'params' => $this->params,
        ];
        
        if ($this->id !== null) {
            $result['id'] = $this->id;
        }
        
        return $result;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getParams(): array
    {
        return $this->params;
    }
    
    public function getId(): ?string
    {
        return $this->id;
    }
    
    public function getJsonRpc(): string
    {
        return $this->jsonrpc;
    }
}
