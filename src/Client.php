<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Support\Facades\Log;

/**
 * Class Client
 * @package Tochka\JsonRpcClient
 *
 * @method static static get(string $serviceName)
 * @method static static batch()
 * @method static static cache($minutes = -1)
 * @method static execute()
 * @method static Response call(string $method, array $params)
 */
class Client
{
    const CODE_PARSE_ERROR = -32700;
    const CODE_INVALID_REQUEST = -32600;
    const CODE_METHOD_NOT_FOUND = -32601;
    const CODE_INVALID_PARAMS = -32602;
    const CODE_INTERNAL_ERROR = -32603;
    const CODE_INVALID_PARAMETERS = 6000;
    const CODE_VALIDATION_ERROR = 6001;
    const CODE_UNAUTHORIZED = 7000;
    const CODE_FORBIDDEN = 7001;
    const CODE_EXTERNAL_INTEGRATION_ERROR = 8000;
    const CODE_INTERNAL_INTEGRATION_ERROR = 8001;

    const HTTP_AUTH_NONE = 'none';
    const HTTP_AUTH_BASIC = 'basic';
    const HTTP_AUTH_DIGEST = 'digest';
    const HTTP_AUTH_NTLM = 'ntlm';
    const HTTP_AUTH_GSS = 'gss';
    const HTTP_AUTH_ANY = 'any';
    const HTTP_AUTH_SAFE = 'safe';

    public static $jsonrpc_messages = [
        self::CODE_PARSE_ERROR                => 'Ошибка обработки запроса',
        self::CODE_INVALID_REQUEST            => 'Неверный запрос',
        self::CODE_METHOD_NOT_FOUND           => 'Указанный метод не найден',
        self::CODE_INVALID_PARAMS             => 'Неверные параметры',
        self::CODE_INTERNAL_ERROR             => 'Внутренняя ошибка',
        self::CODE_INVALID_PARAMETERS         => 'Неверные параметры',
        self::CODE_VALIDATION_ERROR           => 'Ошибка валидации',
        self::CODE_UNAUTHORIZED               => 'Неверный ключ авторизации',
        self::CODE_FORBIDDEN                  => 'Доступ запрещен',
        self::CODE_EXTERNAL_INTEGRATION_ERROR => 'Ошибка внешних сервисов',
        self::CODE_INTERNAL_INTEGRATION_ERROR => 'Ошибка внутренних сервисов',
    ];

    protected $serviceName;

    protected $is_batch = false;

    protected $cache;

    /** @var Request[] */
    protected $requests = [];

    /** @var Response[] */
    protected $results = [];

    private $time = 0;

    public function __construct()
    {
        $this->requests = [];
        $this->results = [];
    }

    public static function __callStatic($method, $params)
    {
        $instance = new static();

        return $instance->$method(...$params);
    }

    public function __call($method, $params)
    {
        if (method_exists($this, '_' . $method)) {
            return $this->{'_' . $method}(...$params);
        }

        return $this->_call($method, $params);
    }

    /**
     * Устанавливает имя сервиса для текущего экземпляра клиента
     *
     * @param string $serviceName
     *
     * @return $this
     */
    protected function _get($serviceName)
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    /**
     * Помечает экземпляр клиента как массив вызовов
     * @return $this
     */
    protected function _batch()
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
    protected function _cache($minutes = -1)
    {
        $this->cache = $minutes;

        return $this;
    }

    /**
     * Выполняет удаленный вызов (либо добавляет его в массив)
     *
     * @param string $method
     * @param array  $params
     *
     * @return Response
     */
    protected function _call($method, $params)
    {
        if (!$this->is_batch) {
            $this->requests = [];
            $this->results = [];
        }

        $request = $this->createRequest($method, $params);
        $this->requests[$request->getId()] = $request;
        $this->results[$request->getId()] = new Response();

        $this->cache = null;

        if (!$this->is_batch) {
            $this->_execute();
        }

        return $this->results[$request->getId()];
    }

    /**
     * Выполняет запрос всех вызовов
     */
    protected function _execute()
    {
        $this->time = microtime(true);

        // имя сервиса
        $serviceName = $this->getServiceName();

        // настройки подключения
        $settings = self::getConnectionOptions($serviceName);

        $headers = ['Content-type: application/json'];

        if (null !== $settings['auth']['headerToken']) {
            $name = isset($settings['auth']['headerToken']['name']) ? $settings['auth']['headerToken']['name'] : '';
            $key = isset($settings['auth']['headerToken']['key']) ? $settings['auth']['headerToken']['key'] : '';

            $headers[] = $name . ': ' . $key;
        }

        // если не заданы настройки хоста
        if (null === $settings['host']) {
            Log::error('No connection settings for the service "' . $serviceName . '');
            $this->result(null, false);

            return;
        }

        // формируем запросы
        $requests = [];

        foreach ($this->requests as $request) {
            if ($request->hasCache()) {
                $result = $request->getCache();
                $this->result($request->getId(), $result->success, $result->data, $result->error);
            } else {
                $requests[] = $request->getRequest();
            }
        }

        // если нет запросов - ничего не делаем
        if (!count($requests)) {
            return;
        }

        if (count($requests) === 1) {
            $requests = $requests[0];
        }

        // запрос
        $json_request = json_encode($requests);

        $curl = curl_init($settings['host']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_request);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        // если необходимо http аутентификация
        if (null !== $settings['auth']['http']) {
            $scheme = isset($settings['auth']['http']['scheme']) ? $settings['auth']['http']['scheme'] : 'none';
            $authType = $this->getHttpAuthScheme($scheme);

            if (null !== $authType) {
                $username = isset($settings['auth']['http']['username']) ? $settings['auth']['http']['username'] : '';
                $password = isset($settings['auth']['http']['password']) ? $settings['auth']['http']['password'] : '';

                curl_setopt($curl, CURLOPT_HTTPAUTH, $authType);
                curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
            }
        }

        $json_response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($json_response);

        // ошибка декодирования Json
        if (null === $response) {
            Log::error('Error parsing response from Api. An error has occurred on the server. ' . $this->getLogInfo($json_request, $json_response));
            $this->result(null, false);

            return;
        }


        if (is_array($response)) {
            // если вернулся массив результатов
            foreach ($response as $result) {
                if (!$this->parseResult($result)) {
                    Log::error('JsonRpc error (' . $serviceName . '). ' . $this->getLogInfo($json_request, $json_response));
                }
            }
        } elseif (!$this->parseResult($response)) {
            Log::error('JsonRpc error (' . $serviceName . '). ' . $this->getLogInfo($json_request, $json_response));
        }

        $this->requests = [];
    }

    /**
     * @param $result
     *
     * @return bool
     */
    protected function parseResult($result)
    {
        if (!empty($result->error)) {
            $this->result(!empty($result->id) ? $result->id : null, false, null, $result->error);

            return false;
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
    protected function result($id, $success, $data = null, $error = null)
    {
        if (null === $id) {
            foreach ($this->results as $key => $value) {
                if (null !== $key) {
                    $this->result($key, $success, $data, $error);
                }
            }
        } else {
            if (!isset($this->results[$id])) {
                $this->results[$id] = new Response();
            }

            $this->results[$id]->success = $success;
            if (null !== $data) {
                $this->results[$id]->data = $data;
            }
            if (null !== $error) {
                $this->results[$id]->error = $error;
            }
        }
    }

    private function getLogInfo($json_request, $json_response)
    {
        return 'Request: ' . var_export($json_request, true) . '<br />Response: ' . var_export($json_response, true);
    }

    /**
     * Возвращает имя текущего сервиса
     * @return string
     */
    public function getServiceName()
    {
        // имя сервиса
        if ($this->serviceName === null) {
            return config('jsonrpcclient.default');
        }

        return $this->serviceName;
    }

    /**
     * Возвращает настройки подключения к сервису
     *
     * @param string $serviceName
     *
     * @return array
     */
    public static function getConnectionOptions($serviceName)
    {
        $headerToken = config('jsonrpcclient.connections.' . $serviceName . '.auth.headerToken', null);
        $httpAuth = config('jsonrpcclient.connections.' . $serviceName . '.auth.http', null);

        $headerToken = self::getOldConnectionOptions($serviceName, $headerToken);

        return [
            'host' => config('jsonrpcclient.connections.' . $serviceName . '.url'),
            'clientClass' => config('jsonrpcclient.connections.' . $serviceName . '.clientClass'),
            'auth' => [
                'headerToken' => $headerToken,
                'http'        => $httpAuth,
            ],
        ];
    }

    /**
     * @param string $serviceName
     * @param array $headerToken
     *
     * @return mixed
     */
    public static function getOldConnectionOptions($serviceName, $headerToken)
    {
        $name = config('jsonrpcclient.connections.' . $serviceName . '.authHeaderName', null);
        $key = config('jsonrpcclient.connections.' . $serviceName . '.key', null);

        if (null === $headerToken && null !== $name && null !== $key) {
            $headerToken = [
                'name' => $name,
                'key'  => $key,
            ];
        }

        return $headerToken;
    }

    /**
     * Возвращает тип авторизации для CURL исходя из нашего алиаса
     *
     * @param $scheme
     *
     * @return int|null
     */
    protected function getHttpAuthScheme($scheme)
    {
        switch ($scheme) {
            case self::HTTP_AUTH_NONE:
                return null;
            case self::HTTP_AUTH_BASIC:
                return CURLAUTH_BASIC;
            case self::HTTP_AUTH_DIGEST:
                return CURLAUTH_DIGEST;
            case self::HTTP_AUTH_GSS:
                return CURLAUTH_GSSNEGOTIATE;
            case self::HTTP_AUTH_NTLM:
                return CURLAUTH_NTLM;
            case self::HTTP_AUTH_ANY:
                return CURLAUTH_ANY;
            case self::HTTP_AUTH_SAFE:
                return CURLAUTH_ANYSAFE;
            default:
                return null;
        }
    }

    /**
     * Создает новый запрос
     *
     * @param string $method
     * @param array  $params
     *
     * @return Request
     */
    protected function createRequest($method, $params)
    {
        return new Request($this->getServiceName(), $method, $params, config('jsonrpcclient.clientName'), $this->cache);
    }
}