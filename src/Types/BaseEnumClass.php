<?php

namespace Tochka\JsonRpcClient\Types;

class BaseEnumClass implements ParameterValue
{
    /** @var mixed */
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function set($value)
    {
        return new static($value);
    }

    public function getValue()
    {
        return $this->value;
    }
}