<?php

namespace Tochka\JsonRpcClient\Tests;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;
use Tochka\JsonRpcClient\Tests\Helpers\BarMiddleware;
use Tochka\JsonRpcClient\Tests\Helpers\BarOnceMiddleware;
use Tochka\JsonRpcClient\Tests\Helpers\FooMiddleware;
use Tochka\JsonRpcClient\Tests\Helpers\FooOnceMiddleware;

class ClientConfigTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\ClientConfig::__construct
     */
    public function testConstructConfigurationMismatch(): void
    {
        $this->expectException(JsonRpcClientException::class);
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
                FooMiddleware::class,
                FooOnceMiddleware::class => [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
                BarMiddleware::class     => [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
                BarOnceMiddleware::class,
            ],
            'queryPreparer' => 'TestQueryPreparer',
            'extendedStubs' => true,
            'options'       => ['timeout' => 666],
        ];

        $middlewareConfigured = [
            [FooMiddleware::class, []],
            [
                BarMiddleware::class,
                [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
            ],
        ];

        $middlewareOnceConfigured = [
            [
                FooOnceMiddleware::class,
                [
                    'foo'   => 'bar',
                    'hello' => 'world',
                ],
            ],
            [BarOnceMiddleware::class, []],
        ];

        $instance = new ClientConfig('clientName', 'serviceName', $data);

        $this->assertEquals('clientName', $instance->clientName);
        $this->assertEquals('serviceName', $instance->serviceName);
        $this->assertEquals($data['url'], $instance->url);
        $this->assertEquals($data['clientClass'], $instance->clientClass);
        $this->assertEquals($middlewareConfigured, $instance->middleware);
        $this->assertEquals($middlewareOnceConfigured, $instance->onceExecutedMiddleware);
        $this->assertEquals($data['queryPreparer'], $instance->queryPreparer);
        $this->assertEquals($data['extendedStubs'], $instance->extendedStubs);
        $this->assertEquals($data['options'], $instance->options);
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
