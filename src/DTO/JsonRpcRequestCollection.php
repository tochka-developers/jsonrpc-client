<?php

namespace Tochka\JsonRpcClient\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Tochka\JsonRpc\Standard\DTO\JsonRpcRequest;

/**
 * @psalm-api
 * @psalm-import-type JsonRpcRequestArray from JsonRpcRequest
 * @psalm-suppress MissingTemplateParam
 */
class JsonRpcRequestCollection implements Arrayable
{
    /** @var array<JsonRpcClientRequest> */
    private array $items;

    /**
     * @param array<JsonRpcClientRequest> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function add(JsonRpcClientRequest $request): void
    {
        $this->items[] = $request;
    }

    public function empty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return array<JsonRpcClientRequest>
     */
    public function get(): array
    {
        return $this->items;
    }

    public function findById(string|int $id): ?JsonRpcClientRequest
    {
        foreach ($this->items as $jsonRpcClientRequest) {
            if ($jsonRpcClientRequest->getRequest()->id === $id) {
                return $jsonRpcClientRequest;
            }
        }

        return null;
    }

    /**
     * @return array<int, JsonRpcRequestArray>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (JsonRpcClientRequest $item) => $item->getRequest()->toArray(),
            array_filter($this->items, fn (JsonRpcClientRequest $item) => $item->getResponse() === null)
        );
    }

    public function getResults(): array
    {
        return array_map(
            static fn (JsonRpcClientRequest $item) => $item->getResult(),
            $this->items
        );
    }
}
