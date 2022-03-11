<?php

namespace Tochka\JsonRpcClient\Tests\QueryPreparers;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\QueryPreparers\ArrayParametersPreparer;
use Tochka\JsonRpcClient\Support\ClientConfig;

class ArrayParametersPreparerTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\QueryPreparers\ArrayParametersPreparer::prepare
     */
    public function testPrepare()
    {
        $clientConfig = new ClientConfig('clientName', 'serviceName', ['url' => 'url', 'clientClass' => 'clientClass']);
        $preparer = new ArrayParametersPreparer();
        $jsonRpcRequest = $preparer->prepare('method', ['param1' => 'value1', 'param2' => 'value2'], $clientConfig);
        $this->assertNotNull($jsonRpcRequest->id);
        $this->assertSame('method', $jsonRpcRequest->method);
        $this->assertSame(['value1', 'value2'], $jsonRpcRequest->params);

    }
}
