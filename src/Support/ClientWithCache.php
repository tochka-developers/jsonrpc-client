<?php

namespace Tochka\JsonRpcClient\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Tochka\JsonRpcClient\Middleware\CacheMiddleware;

trait ClientWithCache
{
    /**
     * @throws BindingResolutionException
     */
    public static function cache(int $ttl): static
    {
        return static::getInstance()->_cache($ttl);
    }

    public function _cache(int $ttl): static
    {
        return $this->_with(CacheMiddleware::DATA_KEY_CACHE, $ttl);
    }

}
