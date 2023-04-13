<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpc\Standard\Support\MiddlewareRegistry as DefaultMiddlewareRegistry;
use Tochka\JsonRpcClient\Contracts\ClientMiddlewareRegistryInterface;

class MiddlewareRegistry extends DefaultMiddlewareRegistry implements ClientMiddlewareRegistryInterface
{
}
