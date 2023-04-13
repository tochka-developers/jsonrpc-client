<?php

namespace Tochka\JsonRpcClient\Route\DTO;

use Tochka\JsonRpcClient\Support\LegacyEnum;

class ParameterTypeEnum extends LegacyEnum
{
    private const TYPE_STRING = 'string';
    private const TYPE_FLOAT = 'float';
    private const TYPE_BOOLEAN = 'boolean';
    private const TYPE_INTEGER = 'integer';
    private const TYPE_OBJECT = 'object';
    private const TYPE_ARRAY = 'array';
    private const TYPE_MIXED = 'mixed';

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_STRING(): self
    {
        return new self(self::TYPE_STRING);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_FLOAT(): self
    {
        return new self(self::TYPE_FLOAT);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_BOOLEAN(): self
    {
        return new self(self::TYPE_BOOLEAN);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_INTEGER(): self
    {
        return new self(self::TYPE_INTEGER);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_OBJECT(): self
    {
        return new self(self::TYPE_OBJECT);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_ARRAY(): self
    {
        return new self(self::TYPE_ARRAY);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function TYPE_MIXED(): self
    {
        return new self(self::TYPE_MIXED);
    }
}
