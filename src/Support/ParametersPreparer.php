<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpcClient\Contracts\ParametersPreparerInterface;
use Tochka\JsonRpcClient\Contracts\RouterInterface;
use Tochka\JsonRpcClient\JsonRpcClient;
use Tochka\JsonRpcClient\Route\DTO\Parameter;
use Tochka\JsonRpcClient\Route\DTO\ParameterTypeEnum;

class ParametersPreparer implements ParametersPreparerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function prepare(JsonRpcClient $client, string $method, array $parameters): array
    {
        $resultParameters = [];

        $route = $this->router->getRoute($client, $method);

        for ($i = 0; $i < count($parameters); $i++) {
            if (!isset($route->parameters[$i])) {
                break;
            }

            if ($route->parameters[$i]->type->is(ParameterTypeEnum::TYPE_OBJECT()) && is_object($parameters[$i])) {
                $value = $this->castObjectValue($route->parameters[$i], $parameters[$i]);
            } elseif ($route->parameters[$i]->type->is(ParameterTypeEnum::TYPE_ARRAY()) && is_array($parameters[$i])) {
                $value = $this->castArrayValue($route->parameters[$i], $parameters[$i]);
            } else {
                $value = $parameters[$i];
            }

            if ($client->_getClientConfig()->parametersByName) {
                $resultParameters[$route->parameters[$i]->name] = $value;
            } else {
                $resultParameters[] = $value;
            }
        }

        return $resultParameters;
    }

    private function castObjectValue(Parameter $parameter, object $value): object
    {
        return $value;
    }

    private function castArrayValue(Parameter $parameter, array $value): array
    {
        return $value;
    }
}
