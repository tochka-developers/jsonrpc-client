<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcSmd\SmdDescription;

/**
 * Class ClientClass
 * @package Tochka\JsonRpcClient\ClientGenerator
 */
class ClientClass extends AbstractClass implements Stub
{
    /** @var SmdDescription */
    protected $smd;

    protected $classDescription;
    protected $methods;
    protected $serviceName;
    protected $methodSource;

    public function __construct(SmdDescription $smd, ClientConfig $config, string $className, string $classNamespace)
    {
        parent::__construct(null, $className, $classNamespace);
        $this->extendedStubs = $config->extendedStubs;
        $this->serviceName = $config->serviceName;

        $this->setSmd($smd);
    }

    public function setSmd(SmdDescription $smd)
    {
        $this->smd = $smd;

        if ($this->extendedStubs) {
            $this->makeCurrentClasses($this->smd->objects);
        }

        $this->makeSource();
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

use Illuminate\Support\Facades\Facade;
{$this->getUses()}
/**
 * {$this->classDescription}@author JsonRpcClientGenerator
 * @date {$this->getDate()}
 * @mixin \Tochka\JsonRpcClient\Client
{$this->methodSource}
 */
class {$this->className} extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return self::class;
    }
}
php;
    }

    protected function getNamedParameters()
    {
        return $this->smd->namedParameters ? 'true' : 'false';
    }

    protected function makeSource()
    {
        $source = [];
        $oldGroup = null;

        // перебираем доступные методы
        foreach ($this->smd->services as $method) {
            $serviceClass = new ServiceClass($this, $method);
            $this->addSubClass($serviceClass);

            // если началась новая группа
            if (isset($method->group)) {
                if ($oldGroup !== $method->group) {
                    $source[] = '';
                    if (!empty($method->groupName)) {
                        $ln = mb_strlen($method->groupName);
                        $delimiter = str_pad('', $ln + 20, '=');
                        $source[] = $delimiter;
                        $source[] = str_pad('', 10) . $method->groupName;
                        $source[] = $delimiter;
                    }
                }
                $oldGroup = $method->group;
            }

            $source = $serviceClass->getMethodDescription($source);
        }

        if (empty($source)) {
            $source = [' *'];
        }

        $this->methodSource = implode("\n * ", $source);
    }
}