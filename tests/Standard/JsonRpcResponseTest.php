<?php

namespace Tochka\JsonRpcClient\Tests\Standard;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\Standard\JsonRpcError;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;

class JsonRpcResponseTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\Standard\JsonRpcResponse::__construct
     */
    public function testConstructResult(): void
    {
        $data = (object) [
            'jsonrpc' => '2.0',
            'id'      => 123,
            'result'  => 'SomeResult',
        ];

        $instance = new JsonRpcResponse($data);
        $this->assertEquals($data->jsonrpc, $instance->jsonrpc);
        $this->assertEquals($data->id, $instance->id);
        $this->assertEquals($data->result, $instance->result);
        $this->assertNull($instance->error);
    }

    /**
     * @covers \Tochka\JsonRpcClient\Standard\JsonRpcResponse::__construct
     */
    public function testConstructError(): void
    {
        $data = (object) [
            'jsonrpc' => '2.0',
            'id'      => 123,
            'error'   => (object) [
                'code'    => 123,
                'message' => 'Some message',
            ],
        ];

        $instance = new JsonRpcResponse($data);
        $this->assertEquals($data->jsonrpc, $instance->jsonrpc);
        $this->assertEquals($data->id, $instance->id);
        $this->assertInstanceOf(JsonRpcError::class, $instance->error);
        $this->assertEquals($data->error->code, $instance->error->code);
        $this->assertEquals($data->error->message, $instance->error->message);
        $this->assertNull($instance->result);
    }
}
