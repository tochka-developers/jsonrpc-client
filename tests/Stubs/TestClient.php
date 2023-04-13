<?php

namespace Tochka\JsonRpcClient\Tests\Stubs;

use Tochka\JsonRpcClient\JsonRpcClient;

class TestClient extends JsonRpcClient
{
    public function test(string $string, array $array)
    {
        return $this->_call(__METHOD__, func_get_args());
    }

    public function foo(string $string, array $array)
    {
        return $this->_call(__METHOD__, func_get_args());
    }

    public function bar(string $string, array $array)
    {
        return $this->_call(__METHOD__, func_get_args());
    }
}
