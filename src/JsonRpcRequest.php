<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Support\Facades\Cache;

class JsonRpcRequest
{
    protected $serviceName;
    protected $method;
    protected $params;
    protected $id;
    protected $cache;

    protected $headers = [];
    protected $body = [];

    /**
     * Request constructor.
     *
     * @param string $serviceName
     * @param string $method
     * @param array $params
     * @param string $clientName
     * @param int $cache
     */
    public function __construct($serviceName, $method, $params, $clientName, $cache)
    {
        $this->serviceName = $serviceName;
        $this->method = $method;
        $this->params = $params;
        $this->id = $this->generateId($clientName);
        $this->cache = $cache;
    }

    /**
     * Возвращает уникальный идентификатор запроса
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Возвращает массив запроса
     * @return array
     */
    public function getRequest(): array
    {
        return [
            'jsonrpc' => '2.0',
            'method'  => $this->method,
            'params'  => $this->params,
            'id'      => $this->id,
        ];
    }

    /**
     * Генерирует уникальный идентификатор запроса
     *
     * @param string $prefix
     *
     * @return string
     */
    public function generateId($prefix = ''): string
    {
        return uniqid($prefix, false);
    }

    /**
     * Возвращает уникальный хеш запроса
     * @return string
     */
    public function getHash(): string
    {
        return $this->serviceName . '.' . $this->method . '?' . md5(json_encode($this->params));
    }

    /**
     * Проверяет, есть ли закешированный результат для данного запроса
     * @return bool
     */
    public function hasCache(): bool
    {
        return $this->cache !== null && Cache::has($this->getHash());
    }

    /**
     * Проверяет, необходимо ли сохранить результат запроса в кеш
     * @return bool
     */
    public function wantCache(): bool
    {
        return $this->cache !== null;
    }

    /**
     * Возвращает результат запроса
     * @return mixed
     */
    public function getCache()
    {
        return Cache::get($this->getHash(), new EmptyResponse());
    }

    /**
     * Сохраняет результат запроса в кеш
     *
     * @param mixed $response
     */
    public function setCache($response): void
    {
        Cache::put($this->getHash(), $response, $this->cache);
    }
}