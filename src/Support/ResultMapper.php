<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpc\Standard\DTO\JsonRpcError;
use Tochka\JsonRpc\Standard\DTO\JsonRpcResponse;
use Tochka\JsonRpc\Standard\Exceptions\JsonRpcException;
use Tochka\JsonRpcClient\Contracts\ResultMapperInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;
use Tochka\JsonRpcClient\DTO\JsonRpcResponseCollection;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;

class ResultMapper implements ResultMapperInterface
{
    public function mapCollection(JsonRpcRequestCollection $requests, JsonRpcResponseCollection $responses): void
    {
        $exceptions = [];

        foreach ($responses->get() as $response) {
            $request = $requests->findById($response->id);
            if ($request === null) {
                continue;
            }

            try {
                $this->map($request, $response);
            } catch (JsonRpcException $e) {
                $exceptions[] = $e;
            }
        }

        if (!empty($exceptions)) {
            throw new JsonRpcClientException();
        }
    }

    public function map(JsonRpcClientRequest $request, JsonRpcResponse $response): void
    {
        $request->setResponse($response);

        if ($response->result !== null) {
            $request->setResult($this->mapResult($response->result));
        }

        if ($response->error !== null) {
            throw $this->mapException($response->error);
        }
    }

    private function mapResult(mixed $result): mixed
    {

        return $result;
    }

    private function mapException(JsonRpcError $error): JsonRpcException
    {
        return new JsonRpcException($error->code, $error->message, $error->data);
    }
}
