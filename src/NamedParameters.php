<?php

namespace Tochka\JsonRpcClient;

use phpDocumentor\Reflection\DocBlockFactory;
use Tochka\JsonRpcClient\DocBlock\Method;

class NamedParameters
{
    /**
     * Задаёт имена параметрам, забирая информацию из описания метода
     *
     * @param string $class
     * @param string $methodName
     * @param array  $params
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getParamsWithNames(string $class, string $methodName, array $params): array
    {
        $docFactory = DocBlockFactory::createInstance(['method' => Method::class]);

        $reflection = new \ReflectionClass($class);
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

        return $inputArguments;
    }
}