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

    public static $jsonrpc_messages = [
        self::CODE_PARSE_ERROR => 'Ошибка обработки запроса',
        self::CODE_INVALID_REQUEST => 'Неверный запрос',
        self::CODE_METHOD_NOT_FOUND => 'Указанный метод не найден',
        self::CODE_INVALID_PARAMS => 'Неверные параметры',
        self::CODE_INTERNAL_ERROR => 'Внутренняя ошибка',
        self::CODE_INVALID_PARAMETERS => 'Неверные параметры',
        self::CODE_VALIDATION_ERROR => 'Ошибка валидации',
        self::CODE_UNAUTHORIZED => 'Неверный ключ авторизации',
        self::CODE_FORBIDDEN => 'Доступ запрещен',
        self::CODE_EXTERNAL_INTEGRATION_ERROR => 'Ошибка внешних сервисов',
        self::CODE_INTERNAL_INTEGRATION_ERROR => 'Ошибка внутренних сервисов',
    ];

    protected $serviceName = null;

    protected $is_batch = false;

    protected $cache = null;

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
        } else {
            return $this->_call($method, $params);
        }
    }

    /**
     * Устанавливает имя сервиса для текущего экземпляра клиента
     * @param string $serviceName
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
     * @param int $time
     * @return $this
     */
    protected function _cache($minutes = -1)
    {
        $this->cache = $minutes;
        return $this;
    }

    /**
     * Выполняет удаленный вызов (либо добавляет его в массив)
     * @param string $method
     * @param array $params
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
        $settings = $this->getConnectionOptions($serviceName);

        $headers = ['Content-type: application/json'];

        if ($settings['authHeader'] !== null && $settings['key'] !== null) {
            $headers[] = $settings['authHeader'] . ': ' . $settings['key'];
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
 
       if (isset($settings['authType']) {
            if (null === $settings['authUsername']) {
                Log::error('No authUsername given in connection settings for the service "' . $serviceName . '"');
            } else if (null === $settings['authPassword']) {
                Log::error('No authPassword given in connection settings for the service "' . $serviceName . '"');
            } else {
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY); // we can adjust this according to "authType" setting, but not this time
                curl_setopt($curl, CURLOPT_USERNAME, $settings['authUsername']);
                curl_setopt($curl, CURLOPT_USERPWD, $settings['authPassword']);
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
        } else {
            if (!$this->parseResult($response)) {
                Log::error('JsonRpc error (' . $serviceName . '). ' . $this->getLogInfo($json_request, $json_response));
            }
        }
        $this->requests = [];
    }

    /**
     * @param $result
     * @return bool
     */
    protected function parseResult($result)
    {
        if (!empty($result->error)) {
            $this->result(!empty($result->id) ? $result->id : null, false, null, $result->error);
            return false;
        } else {
            $this->result(!empty($result->id) ? $result->id : null, true, $result->result);

            // если надо - кешируем результат
            if (!empty($result->id) && $this->requests[$result->id]->wantCache()) {
                $this->requests[$result->id]->setCache($this->results[$result->id]);
            }
            return true;
        }
    }

    /**
     * Заполняет результат указанными данными
     * @param string $id ID вызова. Если NULL, то будет заполнен результат всех вызовов
     * @param bool $success Успешен ли вызов
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
        } else {
            return $this->serviceName;
        }
    }

    /**
     * Возвращает настройки подключения к сервису
     * @param string $serviceName
     * @return array
     */
    protected function getConnectionOptions($serviceName)
    {
        return [
            'host' => config('jsonrpcclient.connections.' . $serviceName . '.url'),
            'key' => config('jsonrpcclient.connections.' . $serviceName . '.key', null),
            'authHeader' => config('jsonrpcclient.connections.' . $serviceName . '.authHeaderName', null),
            'authType' => config('jsonrpcclient.connections.' . $serviceName . '.authType', null),
            'authUsername' => config('jsonrpcclient.connections.' . $serviceName . '.authUsername', null),
            'authPassword' => config('jsonrpcclient.connections.' . $serviceName . '.authPassword', null), 
       ];
    }

    /**
     * Создает новый запрос
     * @param string $method
     * @param array $params
     * @return Request
     */
    protected function createRequest($method, $params)
    {
        return new Request($this->getServiceName(), $method, $params, config('jsonrpcclient.clientName'), $this->cache);
    }
}