<?php

namespace Tochka\JsonRpcClient\Tests\Units;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use Tochka\JsonRpcClient\JsonRpcClientServiceProvider;

abstract class DefaultTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getPackageProviders($app): array
    {
        return [JsonRpcClientServiceProvider::class];
    }
}
