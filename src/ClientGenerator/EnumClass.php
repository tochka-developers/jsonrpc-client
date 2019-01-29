<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcSmd\SmdEnumObject;
use Tochka\JsonRpcSmd\SmdParameter;

class EnumClass extends AbstractClass implements Stub
{
    /** @var SmdParameter[] */
    protected $parameters;

    public $classDescription;
    public $type;
    public $values;
    public $constants;

    public function __construct(AbstractClass $parentClass, string $className, string $classNamespace, array $values, string $type, bool $alias = false)
    {
        parent::__construct($parentClass, $className, $classNamespace);

        if ($alias) {
            $this->aliasName = $parentClass->className . '_' . $className;
        }
        $this->values = $values;
        $this->type = $type;

        $this->makeSource();
    }

    public static function fromObject(AbstractClass $baseClass, SmdEnumObject $object, bool $alias = false)
    {
        $instance = new self($baseClass, $object->name, $baseClass->getFullClassName(), $object->values, $object->type, $alias);

        return $instance;
    }

    public static function fromProperty(AbstractClass $baseClass, SmdParameter $parameter, bool $alias = false)
    {
        $className = studly_case($parameter->name) . 'Enum';

        $type = implode('|', $parameter->types);

        $instance = new self($baseClass, $className, $baseClass->getFullClassName(), (array)$parameter->typeVariants, $type, $alias);

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

use Tochka\JsonRpcClient\Client\BaseEnumClass;

/**
 * {$this->classDescription}@author JsonRpcClientGenerator
 * @date {$this->getDate()}
 */
class {$this->className} extends BaseEnumClass
{
{$this->getConstants()}

}
php;
    }

    protected function makeSource()
    {
        foreach ($this->values as $value) {
            if (empty($value->value)) {
                $this->addConstant($value);
            } elseif (empty($value->description)) {
                $this->addConstant($value->value);
            } else {
                $this->addConstant($value->value, $value->description);
            }
        }
    }

    protected function getConstants()
    {
        return implode("\n", array_map(function ($value) {
            $phpDoc = '    /** @var ' . $this->type . (isset($value['description']) ? ' ' . $value['description'] : '') . ' */';
            $constant = '    public const ' . $value['name'] . ' = ' . var_export($value['value'], true) . ';';

            return $phpDoc . "\n" . $constant;
        }, $this->constants));
    }

    protected function addConstant($value, $description = null)
    {
        $property = [
            'name'  => strtoupper(snake_case($value)),
            'value' => $value,
        ];

        if ($description !== null) {
            $property['description'] = $description;
        }
        $this->constants[] = $property;
    }
}