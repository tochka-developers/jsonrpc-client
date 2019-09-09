<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Tochka\JsonRpcClient\Tests;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\Result;
use Tochka\JsonRpcClient\Exceptions\ResponseException;
use Tochka\JsonRpcClient\Request;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;

class RequestTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\Request::getAdditional
     */
    public function testGetAdditional(): void
    {
        $jsonRpcRequest = new JsonRpcRequest('test', []);
        $instance = new Request($jsonRpcRequest);

        $data = [
            'key1' => 'data1',
            'key2' => 'data2',
        ];

        $instance->setAdditional($data);

        $this->assertEquals($data['key1'], $instance->getAdditional('key1'));
        $this->assertEquals($data['key2'], $instance->getAdditional('key2'));
        $this->assertEquals('default', $instance->getAdditional('key3', 'default'));
        $this->assertNull($instance->getAdditional('key4'));
    }

    /**
     * @covers \Tochka\JsonRpcClient\Request::setJsonRpcResponse
     */
    public function testSetJsonRpcResponse(): void
    {
        $jsonRpcRequest = new JsonRpcRequest('test', []);
        $responseData = (object) [
            'jsonrpc' => '2.0',
            'result'  => true,
            'id'      => '1',
        ];
        $jsonRpcResponse = new JsonRpcResponse($responseData);

        $mock = \Mockery::mock(Request::class, [$jsonRpcRequest]);
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('parseResult')->once();
        $mock->makePartial();

        $mock->setJsonRpcResponse($jsonRpcResponse);

        $this->assertEquals($jsonRpcResponse, $mock->getJsonRpcResponse());

        \Mockery::close();
    }

    /**
     * @covers \Tochka\JsonRpcClient\Request::parseResult
     */
    public function testParseResultSuccess(): void
    {
        $jsonRpcRequest = new JsonRpcRequest('test', []);
        $instance = new Request($jsonRpcRequest);

        $jsonRpcResponse = $this->getSuccessResponse(true);

        $instance->setJsonRpcResponse($jsonRpcResponse);

        $this->assertEquals(true, $instance->getResult()->get());
    }

    /**
     * @covers \Tochka\JsonRpcClient\Request::parseResult
     */
    public function testParseResultError(): void
    {
        $jsonRpcRequest = new JsonRpcRequest('test', []);
        $instance = new Request($jsonRpcRequest);

        $jsonRpcResponse = $this->getErrorResponse('Some error', 123);

        $this->expectException(ResponseException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage('Some error');

        $instance->setJsonRpcResponse($jsonRpcResponse);
    }

    /**
     * @covers \Tochka\JsonRpcClient\Request::__construct
     */
    public function testConstruct(): void
    {
        $jsonRpcRequest = new JsonRpcRequest('test', []);
        $instance = new Request($jsonRpcRequest);

        $this->assertEquals($jsonRpcRequest, $instance->getJsonRpcRequest());
        $this->assertInstanceOf(Result::class, $instance->getResult());
    }

    protected function getRequest($method = 'test', $data = [], $id = 1): Request
    {
        $jsonRpcRequest = new JsonRpcRequest('test', $data, $id);

        return new Request($jsonRpcRequest);
    }

    protected function getSuccessResponse($result, $id = 1): JsonRpcResponse
    {
        $responseData = (object) [
            'jsonrpc' => '2.0',
            'result'  => $result,
            'id'      => $id,
        ];

        return new JsonRpcResponse($responseData);
    }

    protected function getErrorResponse(string $message, int $code, $data = null, $id = 1): JsonRpcResponse
    {
        $error = (object) [
            'message' => $message,
            'code'    => $code,
        ];

        if ($data !== null) {
            $error->data = $data;
        }

        $responseData = (object) [
            'jsonrpc' => '2.0',
            'error'   => $error,
            'id'      => $id,
        ];

        return new JsonRpcResponse($responseData);
    }
}
