<?php

namespace Tochka\JsonRpcClient\Route\DTO;

use Tochka\JsonRpc\Contracts\ApiAnnotationInterface;

class Parameter
{
    public string $name;
    /** @var array<ParameterTypeEnum> */
    public array $types;
    /** @var array<ApiAnnotationInterface> */
    public array $annotations = [];

    public function __construct(string $name, ParameterTypeEnum $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}
