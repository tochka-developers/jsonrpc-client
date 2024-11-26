<?php

namespace Tochka\JsonRpcClient\Tests;

use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\Result;

class EmptyResultTest extends TestCase
{
    /**
     * @covers \Tochka\JsonRpcClient\Result::__toString
     */
    public function test_to_string(): void
    {
        $instance = new Result;

        $this->assertEquals('The response has not yet been initialized', (string) $instance);
    }
}
