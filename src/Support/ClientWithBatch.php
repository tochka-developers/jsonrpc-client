<?php

namespace Tochka\JsonRpcClient\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;

trait ClientWithBatch
{
    /**
     * @param callable(static $client): JsonRpcClientRequest ...$calls
     * @return array
     * @throws BindingResolutionException
     */
    public static function batch(callable ...$calls): array
    {
        return static::getInstance()->_batch(...$calls);
    }

    /**
     * @param callable(static $client): JsonRpcClientRequest ...$calls
     * @return array
     */
    public function _batch(callable ...$calls): array
    {
        $batchRequests = new JsonRpcRequestCollection();

        foreach ($calls as $call) {
            $client = clone $this;
            $client->_executeImmediately(false);

            $batchRequests->add($call($client));
        }

        return $this->_execute($batchRequests);
    }
}
