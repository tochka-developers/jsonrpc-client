<?php

namespace Tochka\JsonRpcClient\QueryPreparers;

use phpDocumentor\Reflection\DocBlockFactory;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Contracts\QueryPreparer;
use Tochka\JsonRpcClient\DocBlock\Method;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class DefaultQueryPreparer implements QueryPreparer
{
    /**
     * @param ClientConfig $config
     * @param string       $methodName
     * @param array        $params
     *
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcRequest
     * @throws \ReflectionException
     */
    public function prepare(string $methodName, array $params, ClientConfig $config): JsonRpcRequest
    {
        $clientFacade = $config->clientClass;

        $docFactory = DocBlockFactory::createInstance(['method' => Method::class]);

        $reflection = new \ReflectionClass($clientFacade);
        $docs = $reflection->getDocComment();

        $docBlock = $docFactory->create($docs);
        /** @var Method[] $methods */
        $methods = $docBlock->getTagsByName('method');

        $inputArguments = [];

        foreach ($methods as $method) {
            if ($method->getMethodName() !== $methodName) {
                continue;
            }

            $arguments = $method->getArguments();

            for ($i = 0, $iMax = \count($params); $i < $iMax; $i++) {
                if (isset($arguments[$i])) {
                    $inputArguments[$arguments[$i]['name']] = $params[$i];
                }
            }
        }

        $id = uniqid($config->clientName, true);

        return new JsonRpcRequest($methodName, $inputArguments, $id);
    }
}