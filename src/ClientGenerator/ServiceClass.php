<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcSmd\SmdService;

class ServiceClass extends AbstractClass
{
    use ParametersTrait;

    /** @var SmdService */
    protected $method;

    public function __construct(AbstractClass $parentClass, SmdService $method)
    {
        parent::__construct($parentClass, studly_case($method->name), $parentClass->getFullClassName());
        $this->method = $method;
    }

    public function getMethodDescription(array $source): array
    {
        $this->makeCurrentClasses($this->method->objects, true);

        // описание для метода
        if (!empty($this->method->description)) {
            $source[] = preg_replace("#\n#iu", "\n *   ", $this->method->description);
        }

        // параметры метода
        $parameters = [];
        foreach ($this->method->parameters as $parameter) {
            [$name, $type, $default] = $this->getParameterInfo($parameter, true);

            $parameters[] = ($type !== null ? $type . ' ' : '') . '$' . $name . ($default !== null ? ' = ' . $default : '');
        }
        $parameters = implode(', ', $parameters);

        $return = 'mixed';
        if (!empty($this->method->returnParameters)) {
            foreach ($this->method->returnParameters as $parameter) {
                if ($parameter->is_root) {
                    [, $type] = $this->getReturnInfo($parameter, true);
                    $return = $type;
                    break;
                }
            }
        } elseif (!empty($this->method->return)) {
            $return = implode('|', $this->method->return->types);
        }
        $return = $return ?: 'mixed';
        $source[] = "@method static Response|{$return} {$this->method->name}({$parameters})";
        $source[] = '';

        return $source;
    }

    public function addUse(AbstractClass $class): void
    {
        $this->parentClass->addUse($class);
    }
}