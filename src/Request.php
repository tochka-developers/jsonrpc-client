<?php

namespace Tochka\JsonRpcClient;

/**
 * Class Request
 * @package Tochka\JsonRpcClient
 */
class Request
{
    private $serviceName;
    private $method;
    private $params;
    private $id;
    private $cache = null;

    /**
     * Request constructor.
     * @param string $method
     * @param string $params
     * @param string $idPrefix
     */
    public function __construct($serviceName, $method, $params, $prefix = '', $cache = null)
    {
        $this->serviceName = $serviceName;
        $this->method = $method;
        $this->params = $params;
        $this->id = $this->generateId($prefix);
        $this->cache = $cache;
    }

    /**
     * Возвращает уникальный идентификатор запроса
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Возвращает массив запроса
     * @return array
     */
    public function getRequest()
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $this->method,
            'params' => $this->params,
            'id' => $this->id
        ];
    }

    /**
     * Генерирует уникальный идентификатор запроса
     * @param string $prefix
     * @return string
     */
    public function generateId($prefix = '')
    {
        return uniqid($prefix);
    }

    /**
     * Возвращает уникальный хеш запроса
     * @return string
     */
    public function getHash()
    {
        return $this->serviceName . '.' . $this->method . '?' . md5(json_encode($this->params));
    }

    /**
     * Проверяет, есть ли закешированный результат для данного запроса
     * @return bool
     */
    public function hasCache()
    {
        return $this->cache !== null && \Cache::has($this->getHash());
    }

    /**
     * Проверяет, необходимо ли сохранить результат запроса в кеш
     * @return bool
     */
    public function wantCache()
    {
        return $this->cache !== null;
    }

    /**
     * Возвращает результат запроса
     * @return Response
     */
    public function  getCache()
    {
        return \Cache::get($this->getHash(), new Response());
    }

    /**
     * Сохраняет результат запроса в кеш
     * @param Response $response
     */
    public function setCache($response)
    {
        \Cache::put($this->getHash(), $response, $this->cache);
    }
}