<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcSmd\SmdParameter;

trait ParametersTrait
{
    public function getParameterInfo(SmdParameter $parameter, bool $withAlias = false): array
    {
        $type = 'mixed';

        // если есть вложенные параметры - это локальным объект
        if (!empty($parameter->parameters)) {
            if ($this->extendedStubs) {
                $type = $this->getObjectParameter($parameter, $withAlias);

                $type .= (!empty($parameter->array) ? '[]' : '') . '|object' . (!empty($parameter->array) ? '[]' : '') . '|array';
            } else {
                $type = 'object' . (!empty($parameter->array) ? '[]' : '') . '|array';
            }

        } elseif (!empty($parameter->typeAdditional)) {
            // если указан дополнительный тип - это либо локальный enum, либо ссылка на внешний объект
            if ($parameter->typeAdditional === 'enum') {
                // это локальный enum
                if ($this->extendedStubs) {
                    $type = $this->getEnumParameter($parameter, $withAlias);

                    $type .= (!empty($parameter->array) ? '[]' : '') . '|' . $this->getTypes($parameter);
                } else {
                    $type = $this->getTypes($parameter);
                }
            } elseif ($this->extendedStubs) {
                // это ссылка на внешний объект
                $class = $this->searchCurrentClass($parameter->typeAdditional);
                if ($class === null) {
                    $type = 'mixed';
                } else {
                    $this->addUse($class);
                    $type = $class->aliasName ?? $class->className;
                    $type .= !empty($parameter->array) ? '[]' : '';

                    if ($class instanceof EnumClass) {
                        $type .= '|' . $class->type . (!empty($parameter->array) ? '[]' : '');
                    } else {
                        $type .= '|object' . (!empty($parameter->array) ? '[]' : '') . '|array';
                    }

                }
            } else {
                $type = 'object' . (!empty($parameter->array) ? '[]' : '') . '|array';
            }
        } elseif (!empty($parameter->types)) {
            // если указано несколько типов
            $type = $this->getTypes($parameter);
        } elseif (!empty($parameter->array)) {
            $type = 'array';
        }

        $default = null;
        if (isset($parameter->default)) {
            if (\is_string($parameter->default)) {
                $default = var_export($parameter->default, true);
            } elseif (\is_array($parameter->default)) {
                $default = '[]';
            } else {
                $default = strtolower(var_export($parameter->default, true));
            }
        } elseif (!empty($parameter->optional)) {
            $default = 'null';
        }

        if (empty($type)) {
            $type = 'mixed';
        }

        return [$parameter->name, $type, $default, $parameter->description];
    }

    public function getReturnInfo(SmdParameter $parameter, bool $withAlias = false): array
    {
        $type = 'mixed';

        // если есть вложенные параметры - это локальным объект
        if (!empty($parameter->parameters)) {
            if ($this->extendedStubs) {
                $type = $this->getObjectParameter($parameter, $withAlias, 'Return');

                $type .= (!empty($parameter->array) ? '[]' : '');
            } else {
                $type = 'object' . (!empty($parameter->array) ? '[]' : '') . '|array';
            }

        } elseif (!empty($parameter->typeAdditional)) {
            // если указан дополнительный тип - это либо локальный enum, либо ссылка на внешний объект
            if ($parameter->typeAdditional === 'enum') {
                // это локальный enum
                if ($this->extendedStubs) {
                    $this->getEnumParameter($parameter, $withAlias);
                }

                $type = $this->getTypes($parameter);
            } elseif ($this->extendedStubs) {
                // это ссылка на внешний объект
                $class = $this->searchCurrentClass($parameter->typeAdditional);
                if ($class === null) {
                    $type = 'mixed';
                } else {
                    $this->addUse($class);
                    $type = $class->aliasName ?? $class->className;
                    $type .= !empty($parameter->array) ? '[]' : '';

                    if ($class instanceof EnumClass) {
                        $type = $class->type . (!empty($parameter->array) ? '[]' : '');
                    }

                }
            } else {
                $type = 'object' . (!empty($parameter->array) ? '[]' : '') . '|array';
            }
        } elseif (!empty($parameter->types)) {
            // если указано несколько типов
            $type = $this->getTypes($parameter);
        } elseif (!empty($parameter->array)) {
            $type = 'array';
        }

        if (empty($type)) {
            $type = 'mixed';
        }

        return [$parameter->name, $type, $parameter->description];
    }

    protected function getTypes(SmdParameter $parameter): string
    {
        $types = $parameter->types;

        if (!empty($parameter->array)) {
            $types = array_map(function ($value) {
                return $value . '[]';
            }, $parameter->types);
        }

        return implode('|', $types);
    }

    protected function getObjectParameter(SmdParameter $parameter, bool $withAlias = false, string $postfix = 'Parameter'): string
    {
        /** @var AbstractClass $this */
        $subClass = SubClass::fromProperty($this, $parameter, $withAlias, $postfix);
        $this->addUse($subClass);
        $this->addSubClass($subClass);

        return $subClass->aliasName ?? $subClass->className;
    }

    protected function getEnumParameter(SmdParameter $parameter, bool $withAlias = false): string
    {
        /** @var AbstractClass $this */
        $subClass = EnumClass::fromProperty($this, $parameter, $withAlias);
        $this->addUse($subClass);
        $this->addSubClass($subClass);

        return $subClass->aliasName ?? $subClass->className;
    }
}