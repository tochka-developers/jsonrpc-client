<?php

namespace Tochka\JsonRpcClient\Tests\Standard;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\Standard\JsonRpcError;

class JsonRpcErrorTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\Standard\JsonRpcError::__construct
     */
    public function testConstructFull(): void
    {
        $data = (object) [
            'code'    => 123,
            'message' => 'Test message',
            'data'    => (object) [
                'foo'   => 'bar',
                'hello' => 'world',
            ],
        ];
        $instance = new JsonRpcError($data);

        $this->assertEquals($data->code, $instance->code);
        $this->assertEquals($data->message, $instance->message);
        $this->assertEquals($data->data, $instance->data);
    }

    /**
     * @covers \Tochka\JsonRpcClient\Standard\JsonRpcError::__construct
     */
    public function testConstructDefault(): void
    {
        $data = (object) [
            'code'    => 123,
            'message' => 'Test message',
        ];
        $instance = new JsonRpcError($data);

        $this->assertEquals($data->code, $instance->code);
        $this->assertEquals($data->message, $instance->message);
        $this->assertInstanceOf(\stdClass::class, $instance->data);
    }
}
