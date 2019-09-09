<?php

namespace Tochka\JsonRpcClient\Tests\Standard;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class JsonRpcRequestTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\Standard\JsonRpcRequest::toArray
     */
    public function testToArray(): void
    {
        $params = ['foo' => 'bar', 'hello' => 'world'];
        $instance = new JsonRpcRequest('test', $params, 123);

        $result = $instance->toArray();

        $this->assertEquals('test', $result['method']);
        $this->assertEquals('2.0', $result['jsonrpc']);
        $this->assertEquals($params, $result['params']);
        $this->assertEquals(123, $result['id']);
    }
}
