<?php

namespace Tochka\JsonRpcClient;

class EmptyResponse
{
    public function __toString()
    {
        return 'The response has not yet been initialized';
    }
}