<?php

namespace Tochka\JsonRpcClient\Tests;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;

class ClientConfigTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\ClientConfig::__construct
     */
    public function testConstructConfigurationMismatch(): void
    {
        $this->expectException(\RuntimeException::class);
        new ClientConfig('clientName', 'serviceName', []);
    }

    /**
     * @covers \Tochka\JsonRpcClient\ClientConfig::__construct
     */
    public function testConstructFull(): void
    {
        $data = [
            'url'           => 'http://test.com/jsonrpc',
            'clientClass'   => 'MyStubClass',
            'middleware'    => [
                'MyMiddleware1',
                'MyMiddleware2' => [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
            ],
            'queryPreparer' => 'TestQueryPreparer',
            'extendedStubs' => true,
        ];

        $middlewareConfigured = [
            ['MyMiddleware1', []],
            [
                'MyMiddleware2',
                [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
            ],
        ];

        $instance = new ClientConfig('clientName', 'serviceName', $data);

        $this->assertEquals('clientName', $instance->clientName);
        $this->assertEquals('serviceName', $instance->serviceName);
        $this->assertEquals($data['url'], $instance->url);
        $this->assertEquals($data['clientClass'], $instance->clientClass);
        $this->assertEquals($middlewareConfigured, $instance->middleware);
        $this->assertEquals($data['queryPreparer'], $instance->queryPreparer);
        $this->assertEquals($data['extendedStubs'], $instance->extendedStubs);
    }

    /**
     * @covers \Tochka\JsonRpcClient\ClientConfig::__construct
     */
    public function testConstructDefault(): void
    {
        $data = [
            'url'         => 'http://test.com/jsonrpc',
            'clientClass' => 'MyStubClass',
        ];

        $config = new ClientConfig('clientName', 'serviceName', $data);

        $this->assertEquals('clientName', $config->clientName);
        $this->assertEquals('serviceName', $config->serviceName);
        $this->assertEquals($data['url'], $config->url);
        $this->assertEquals($data['clientClass'], $config->clientClass);
        $this->assertEquals([], $config->middleware);
        $this->assertEquals(DefaultQueryPreparer::class, $config->queryPreparer);
        $this->assertEquals(false, $config->extendedStubs);
    }
}
