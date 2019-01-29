<?php

namespace Tochka\JsonRpcClient;

use GuzzleHttp\RequestOptions;

class HttpClient
{
    protected $client;
    protected $uri;
    protected $options;

    public function __construct($uri)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->uri = $uri;
    }

    /**
     * Устанавливает параметр клиента
     *
     * @param $name
     * @param $value
     */
    public function setOption($name, $value): void
    {
        $this->options[$name] = $value;
    }

    /**
     * Возвращает все параметры клиента
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Возвращает значение параметра клиента
     *
     * @param $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Устанавливает заголовок
     *
     * @param $name
     * @param $value
     */
    public function setHeader($name, $value): void
    {
        $this->options[RequestOptions::HEADERS][$name] = $value;
    }

    /**
     * Возврашает значение заголовка
     *
     * @param $name
     *
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->options[RequestOptions::HEADERS][$name] ?? null;
    }

    /**
     * Возвращает все установленные заголовки
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->options[RequestOptions::HEADERS] ?? [];
    }

    /**
     * Устанавливает содержимое запроса
     *
     * @param array $body
     */
    public function setBody(array $body): void
    {
        $this->options[RequestOptions::JSON] = $body;
    }

    /**
     * Возвращает содержимое запроса
     * @return mixed
     */
    public function getBody()
    {
        return $this->options[RequestOptions::JSON];
    }

    /**
     * Выполняет запрос
     * @return string
     */
    public function get(): string
    {
        return $this->client->post($this->uri, $this->options)->getBody();
    }
}