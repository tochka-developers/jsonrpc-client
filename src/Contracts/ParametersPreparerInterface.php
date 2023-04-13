<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\JsonRpcClient;

interface ParametersPreparerInterface
{
    public function prepare(JsonRpcClient $client, string $method, array $parameters): array;
}
