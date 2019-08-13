<?php

namespace Tochka\JsonRpcClient;

use Tochka\JsonRpcClient\Contracts\Middleware;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\Exceptions\ResponseException;

/**
 * Class Client
 *
 * @package Tochka\JsonRpcClient
 * @method static static get(string $serviceName)
 * @method static static batch()
 * @method static static cache($minutes = -1)
 * @method static execute()
 * @method static EmptyResponse call(string $method, array $params)
 */
class Client
{
    protected $serviceName;
    protected $namedParameters = true;
    protected $config;

    protected $is_batch = false;

    protected $cache;

    /** @var JsonRpcRequest[] */
    protected $requests = [];

    /** @var array */
    protected $results = [];

    public function __construct($serviceName = null, $namedParameters = true)
    {
        $this->requests = [];
        $this->results = [];
        $this->serviceName = $serviceName;
        $this->namedParameters = $namedParameters;
        $this->config = Config::create($this->serviceName);
    }

    public static function __callStatic($method, $params)
    {
        $instance = new static();

        return $instance->$method(...$params);
    }

    /**
     * @param $method
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $params)
    {
        if (method_exists($this, '_' . $method)) {
            return $this->{'_' . $method}(...$params);
        }

        return $this->_call($method, $params);
    }

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Устанавливает имя сервиса для текущего экземпляра клиента
     *
     * @param string $serviceName
     *
     * @return $this
     */
    protected function _get($serviceName): self
    {
        $this->serviceName = $serviceName;
        $this->config = Config::create($serviceName);

        return $this;
    }

    /**
     * Помечает экземпляр клиента как массив вызовов
     *
     * @return $this
     */
    protected function _batch(): self
    {
        $this->requests = [];
        $this->results = [];
        $this->is_batch = true;

        return $this;
    }

    /**
     * Помечает вызываемый метод кешируемым
     *
     * @param int $minutes
     *
     * @return $this
     */
    protected function _cache($minutes = -1): self
    {
        $this->cache = $minutes;

        return $this;
    }

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Выполняет удаленный вызов (либо добавляет его в массив)
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     * @throws \Exception
     */
    protected function _call($method, $params)
    {
        if ($this->namedParameters) {
            $params = NamedParameters::getParamsWithNames($this->config->clientClass, $method, $params);
        }

        if (!$this->is_batch) {
            $this->requests = [];
            $this->results = [];
        }

        $request = new JsonRpcRequest(
            $this->config->serviceName,
            $method,
            $params,
            $this->config->clientName,
            $this->cache
        );

        $this->requests[$request->getId()] = $request;
        $this->results[$request->getId()] = new EmptyResponse();

        $this->cache = null;

        if (!$this->is_batch) {
            $this->_execute();
        }

        return $this->results[$request->getId()];
    }

    /**
     * Выполняет запрос всех вызовов
     *
     * @throws JsonRpcClientException
     */
    protected function _execute(): void
    {
        $client = new HttpClient($this->config->url);

        $requests = $this->getRequestsBody();

        if (!\count($requests)) {
            return;
        }
        if (\count($requests) === 1) {
            $requests = $requests[0];
        }

        $client->setBody($requests);

        foreach ($this->config->middleware as $middleware => $options) {
            $this->handleMiddleware($client, $middleware, $options);
        }

        $json_response = $client->get();
        $response = json_decode($json_response);

        // ошибка декодирования Json
        if (null === $response) {
            throw new JsonRpcClientException(JsonRpcClientException::CODE_RESPONSE_PARSE_ERROR);
        }

        if (\is_array($response)) {
            // если вернулся массив результатов
            foreach ($response as $result) {
                $this->parseResult($result);
            }
        } else {
            $this->parseResult($response);
        }

        $this->requests = [];
    }

    /**
     * Возвращает содержимое всех запросов
     *
     * @return array
     */
    protected function getRequestsBody(): array
    {
        $requests = [];

        foreach ($this->requests as $request) {
            if ($request->hasCache()) {
                $result = $request->getCache();
                $this->result($request->getId(), $result->success, $result->data, $result->error);
            } else {
                $requests[] = $request->getRequest();
            }
        }

        return $requests;
    }

    /**
     * Вызывает обработчики
     *
     * @param $client
     * @param $middleware
     * @param $options
     */
    protected function handleMiddleware($client, $middleware, $options): void
    {
        if (\is_array($options)) {
            $instance = new $middleware($options);
        } else {
            $instance = new $options();
        }

        if (!$instance instanceof Middleware) {
            throw new \LogicException(\get_class($instance) . ' must be instance of Tochka\JsonRpcClient\Contracts\Middleware interface');
        }

        $instance->handle($client, $this->config);
    }

    /**
     * @param $result
     *
     * @return bool
     * @throws ResponseException
     */
    protected function parseResult($result): bool
    {
        if (!empty($result->error)) {
            throw new ResponseException($result->error);
        }

        $this->result(!empty($result->id) ? $result->id : null, true, $result->result);

        // если надо - кешируем результат
        if (!empty($result->id) && $this->requests[$result->id]->wantCache()) {
            $this->requests[$result->id]->setCache($this->results[$result->id]);
        }

        return true;
    }

    /**
     * Заполняет результат указанными данными
     *
     * @param string $id ID вызова. Если NULL, то будет заполнен результат всех вызовов
     * @param bool   $success Успешен ли вызов
     * @param object $data Ответ вызова
     * @param object $error Текст ошибки
     */
    protected function result($id, $success, $data = null, $error = null): void
    {
        if (null === $id) {
            foreach ($this->results as $key => $value) {
                if (null !== $key) {
                    $this->result($key, $success, $data, $error);
                }
            }
        } else {
            $this->results[$id] = $data;
        }
    }
}