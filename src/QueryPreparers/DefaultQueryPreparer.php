<?php

namespace Tochka\JsonRpcClient\QueryPreparers;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Contracts\QueryPreparer;
use Tochka\JsonRpcClient\DocBlock\Method;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class DefaultQueryPreparer implements QueryPreparer
{
    protected $methods = [];

    /**
     * @param \Tochka\JsonRpcClient\ClientConfig $config
     *
     * @return void
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    protected function mapMethods(ClientConfig $config): void
    {
        $clientFacade = $config->clientClass;

        $docFactory = DocBlockFactory::createInstance(['method' => Method::class]);

        try {
            $reflection = new \ReflectionClass($clientFacade);
        } catch (\ReflectionException $e) {
            throw new JsonRpcClientException(0, 'Cannot parse proxy class DocBlock: ' . $e->getMessage());
        }

        $docs = $reflection->getDocComment();

        $docBlock = $docFactory->create($docs);
        /** @var Method[] $methods */
        $methods = $docBlock->getTagsByName('method');
        foreach ($methods as $method) {
            $this->methods[$method->getMethodName()] = $method;
        }
    }

    /**
     * @param        $value
     * @param array  $argument
     * @param string $method
     *
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    protected function checkType($value, array $argument, string $method): void
    {
        $typesArray = [];
        $argumentName = $argument['name'];
        $type = $argument['type'];
        if ($type instanceof Compound) {
            foreach ($type as $item) {
                $typesArray[] = $item;
            }
        } else {
            $typesArray[] = $type;
        }

        foreach ($typesArray as $singleType) {
            $class = get_class($singleType);
            switch ($class) {
                case Null_::class:
                    if (is_null($value)) {
                        return;
                    }
                    break;
                case Boolean::class:
                    if (is_bool($value)) {
                        return;
                    }
                    break;
                case Integer::class:
                    if (is_int($value)) {
                        return;
                    }
                    break;
                case Float_::class:
                    if (is_float($value)) {
                        return;
                    }
                    break;
                case String_::class:
                    if (is_string($value)) {
                        return;
                    }
                    break;
                case Object_::class:
                    if (is_object($value)) {
                        return;
                    }
                    break;
                case Array_::class;
                    if (is_array($value)) {
                        return;
                    }
                    break;
            }
        }

        $messageType = 'expected ' . (string) $type . ' got ' . gettype($value) . ' in method ' . $method;
        throw new JsonRpcClientException(0, 'invalid param ' . $argumentName . ', ' . $messageType);
    }


    /**
     * @param string       $methodName
     * @param array        $params
     * @param ClientConfig $config
     *
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcRequest
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    public function prepare(string $methodName, array $params, ClientConfig $config): JsonRpcRequest
    {
        if (empty($this->methods)) {
            $this->mapMethods($config);
        }

        $inputArguments = [];
        $method = $this->methods[$methodName] ?? null;
        if (!$method) {
            throw new JsonRpcClientException(0, 'Method not found in proxy class');
        }
        $arguments = $method->getArguments();

        for ($i = 0, $iMax = \count($params); $i < $iMax; $i++) {
            if (isset($arguments[$i])) {
                $this->checkType($params[$i], $arguments[$i], $methodName);
                $inputArguments[$arguments[$i]['name']] = $params[$i];
            }
        }

        $id = uniqid($config->clientName, true);

        return new JsonRpcRequest($methodName, $inputArguments, $id);
    }
}
