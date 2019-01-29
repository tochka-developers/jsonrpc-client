<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcSmd\SmdBaseObject;
use Tochka\JsonRpcSmd\SmdEnumObject;
use Tochka\JsonRpcSmd\SmdSimpleObject;

abstract class AbstractClass
{
    public $aliasName;
    public $className;
    public $classNamespace;
    public $extendedStubs = false;
    protected $uses = [];
    /** @var self[] */
    protected $subClasses = [];
    /** @var self */
    protected $parentClass;

    public function __construct(?AbstractClass $parentClass, string $className, string $classNamespace)
    {
        $this->className = $className;
        $this->parentClass = $parentClass;
        $this->classNamespace = $classNamespace;

        if ($parentClass !== null) {
            $this->extendedStubs = $parentClass->extendedStubs;
        }
    }

    protected function getUses(): string
    {
        return implode("\n", array_map(function ($value) {
                if (isset($value['aliasName'])) {
                    return 'use ' . $value['className'] . ' as ' . $value['aliasName'] . ';';
                }

                return 'use ' . $value['className'] . ';';
            }, $this->uses)) . "\n";
    }

    public function addUse(AbstractClass $class): void
    {
        $use = ['className' => $class->getFullClassName()];

        if ($class->aliasName !== null) {
            $use['aliasName'] = $class->aliasName;
        }
        $this->uses[$class->getFullClassName()] = $use;
    }

    /**
     * @return AbstractClass[]
     */
    public function getSubClasses(): array
    {
        $result = [];
        foreach ($this->subClasses as $subClass) {
            $result[] = $subClass->getSubClasses();
        }

        return array_merge($this->subClasses, ...$result);
    }

    /**
     * @return self[]
     */
    public function getCurrentClasses(): array
    {
        $classes = [];
        if ($this->parentClass !== null) {
            $classes = $this->parentClass->getCurrentClasses();
        }

        return array_merge($this->subClasses, $classes);
    }

    /**
     * @param SmdBaseObject[] $objects
     * @param bool $withAlias
     */
    protected function makeCurrentClasses(array $objects, bool $withAlias = false): void
    {
        foreach ($objects as $object) {
            if ($object instanceof SmdEnumObject) {
                $this->addSubClass(EnumClass::fromObject($this, $object, $withAlias));
            }
            if ($object instanceof SmdSimpleObject) {
                $this->addSubClass(SubClass::fromObject($this, $object, $withAlias));
            }
        }
    }

    protected function addSubClass(AbstractClass $class): void
    {
        $this->subClasses[$class->getFullClassName()] = $class;
    }

    public function getFullClassName(string $namespacePostfix = null): string
    {
        $result = $this->classNamespace . '\\' . $this->className;
        if ($namespacePostfix !== null) {
            $result .= '\\' . $namespacePostfix;
        }

        return $result;
    }

    public function searchCurrentClass(string $name): ?self
    {
        $classes = $this->getCurrentClasses();

        foreach ($classes as $class) {
            if ($class->className === $name) {
                return $class;
            }
        }

        return null;
    }

    public function getDate(): string
    {
        return date('d.m.Y H:i');
    }
}