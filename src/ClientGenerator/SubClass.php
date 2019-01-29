<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcSmd\SmdParameter;
use Tochka\JsonRpcSmd\SmdSimpleObject;

class SubClass extends AbstractClass implements Stub
{
    use ParametersTrait;

    /** @var SmdParameter[] */
    protected $parameters;

    public $classDescription;
    public $properties;

    public function __construct(AbstractClass $parentClass, string $className, string $classNamespace, array $parameters, bool $alias = false)
    {
        parent::__construct($parentClass, $className, $classNamespace);

        if ($alias) {
            $this->aliasName = $parentClass->className . '_' . $className;
        }
        $this->parameters = $parameters;

        $this->makeSource();
    }

    public static function fromProperty(AbstractClass $baseClass, SmdParameter $parameter, bool $alias = false, string $postfix = 'Parameter')
    {
        if (!empty($parameter->typeAdditional)) {
            $className = $parameter->typeAdditional;
        } else {
            $className = studly_case($parameter->name) . $postfix;
        }

        $instance = new self($baseClass, $className, $baseClass->getFullClassName(), $parameter->parameters, $alias);

        return $instance;
    }

    public static function fromObject(AbstractClass $baseClass, SmdSimpleObject $object, bool $alias = false)
    {
        $instance = new self($baseClass, $object->name, $baseClass->getFullClassName(), $object->parameters, $alias);

        return $instance;
    }

    public function __toString()
    {
        return $this->getClassSource();
    }

    public function getClassSource(): string
    {
        return <<<php
<?php

namespace {$this->classNamespace};

use Tochka\JsonRpcClient\Client\BaseObjectClass;
{$this->getUses()}
/**
 * {$this->classDescription}@author JsonRpcClientGenerator
 * @date {$this->getDate()}
 */
class {$this->className} extends BaseObjectClass
{
{$this->getProperties()}
}
php;
    }

    protected function makeSource()
    {
        foreach ($this->parameters as $parameter) {
            [$name, $type, $default] = $this->getParameterInfo($parameter);
            $this->addProperty($name, $type, $default, $parameter->description);
        }
    }

    protected function getProperties()
    {
        return implode("\n", array_map(function ($value) {
            $phpDoc = '    /** @var ' . $value['type'] . (isset($value['description']) ? ' ' . $value['description'] : '') . ' */';
            $property = '    public $' . $value['name'] . (isset($value['default']) ? ' = ' . $value['default'] : '') . ';';

            return $phpDoc . "\n" . $property;
        }, $this->properties));
    }

    protected function addProperty($name, $type, $default = null, $description = null)
    {
        $property = [
            'name' => $name,
            'type' => $type,
        ];

        if ($default !== null) {
            $property['default'] = $default;
        }

        if ($description !== null) {
            $property['description'] = $description;
        }

        $this->properties[] = $property;
    }
}