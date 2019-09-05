<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Config;
use Tochka\JsonRpcClient\Contracts\Middleware;
use Tochka\JsonRpcClient\HttpClient;

/**
 * A middleware allowing for inclusion of additional http headers into the request.
 *
 * @package App\Api\Middleware
 */
class AdditionalHeadersMiddleware implements Middleware
{
    protected $name;
    protected $values;

    public function __construct($options)
    {
        $this->values = $options;
    }

    public function handle(HttpClient $client, Config $config): void
    {
        foreach ($this->values as $key => $value) {
            if (!\is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $element) {
                $client->setHeader($key, $element);
            }
        }
    }
}
