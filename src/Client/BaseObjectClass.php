<?php

namespace Tochka\JsonRpcClient\Client;

class BaseObjectClass implements ParameterValue
{
    public function getValue()
    {
        return (object)$this;
    }
}