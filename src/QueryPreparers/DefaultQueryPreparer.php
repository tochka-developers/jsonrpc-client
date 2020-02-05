<?php

namespace Tochka\JsonRpcClient\QueryPreparers;

use phpDocumentor\Reflection\DocBlockFactory;
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
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    protected function mapMethods(ClientConfig $config)
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
     * cast primitive types
     *
     * @param $param
     * @param $argument
     *
     * @return mixed
     */
    protected function castType($param, $argument)
    {
        $typeObject = $argument['type'];
        if (!\in_array((string) $typeObject, [
            'bool',
            'int',
            'float',
            'string',
            'null',
        ])) {
            return $param;
        }

        \settype($param, (string) $typeObject);

        return $param;
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
                $inputArguments[$arguments[$i]['name']] = $this->castType($params[$i], $arguments[$i]);
            }
        }

        $id = uniqid($config->clientName, true);

        return new JsonRpcRequest($methodName, $inputArguments, $id);
    }
}
