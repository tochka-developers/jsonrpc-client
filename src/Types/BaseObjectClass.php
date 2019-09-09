<?php

namespace Tochka\JsonRpcClient\Types;

class BaseObjectClass implements ParameterValue
{
    public function getValue()
    {
        return (object)$this;
    }
}