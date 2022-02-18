<?php

namespace Tochka\JsonRpcClient\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;

class HttpClient implements TransportClient
{
    protected $client;
    protected $options;

    public function __construct($options = [])
    {
        $this->options = $options;
        $this->client = new Client();
    }

    /**
     * Устанавливает параметр клиента
     *
     * @param $name
     * @param $value
     *
     * @codeCoverageIgnore
     */
    public function setOption($name, $value): void
    {
        $this->options[$name] = $value;
    }

    /**
     * Возвращает все параметры клиента
     *
     * @return array
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    public function getHeader($name)
    {
        return $this->options[RequestOptions::HEADERS][$name] ?? null;
    }

    /**
     * Возвращает все установленные заголовки
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getHeaders(): array
    {
        return $this->options[RequestOptions::HEADERS] ?? [];
    }

    /**
     * Устанавливает содержимое запроса
     *
     * @param array $body
     *
     * @codeCoverageIgnore
     */
    public function setBody(array $body): void
    {
        $this->options[RequestOptions::JSON] = $body;
    }

    /**
     * Возвращает содержимое запроса
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getBody()
    {
        return $this->options[RequestOptions::JSON];
    }

    /**
     * Выполняет запрос
     *
     * @param JsonRpcRequest[] $requests
     * @param ClientConfig     $config
     *
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcResponse[]
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    public function get(array $requests, ClientConfig $config): array
    {
        $requests = array_map(static function (JsonRpcRequest $request) {
            return $request->toArray();
        }, $requests);

        $body = \count($requests) === 1 ? $requests[0] : $requests;
        $this->setBody($body);

        try {
            $rawResponse = $this->client->post($config->url, $this->options)->getBody();
            $responses = \GuzzleHttp\json_decode($rawResponse, false);
        } catch (RequestException $e) {
            throw new JsonRpcClientException(JsonRpcClientException::CODE_HTTP_REQUEST_ERROR, null, $e);
        } catch (InvalidArgumentException $e) {
            throw new JsonRpcClientException(JsonRpcClientException::CODE_RESPONSE_PARSE_ERROR, null, $e);
        } catch (\Exception $e) {
            throw new JsonRpcClientException(JsonRpcClientException::CODE_UNKNOWN_REQUEST_ERROR, null, $e);
        }

        if (!\is_array($responses)) {
            $responses = [$responses];
        }

        $result = [];

        foreach ($responses as $response) {
            $result[] = new JsonRpcResponse($response);
        }

        return $result;
    }
}