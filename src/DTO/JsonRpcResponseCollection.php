<?php

namespace Tochka\JsonRpcClient\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Tochka\JsonRpc\Standard\DTO\JsonRpcResponse;

/**
 * @psalm-api
 * @psalm-import-type JsonRpcResponseArray from JsonRpcResponse
 * @psalm-suppress MissingTemplateParam
 */
class JsonRpcResponseCollection implements Arrayable
{
    /** @var array<JsonRpcResponse> */
    private array $items;

    /**
     * @param array<JsonRpcResponse> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function add(JsonRpcResponse $response): void
    {
        $this->items[] = $response;
    }

    public function empty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return array<JsonRpcResponse>
     */
    public function get(): array
    {
        return $this->items;
    }

    /**
     * @return array<int, JsonRpcResponseArray>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (JsonRpcClientRequest $item) => $item->toArray(),
            $this->items
        );
    }
}
