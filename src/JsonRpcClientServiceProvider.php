<?php

namespace Tochka\JsonRpcClient;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Tochka\JsonRpcClient\Contracts\ClientMiddlewareRegistryInterface;
use Tochka\JsonRpcClient\Contracts\ParametersPreparerInterface;
use Tochka\JsonRpcClient\Contracts\ResponseParserInterface;
use Tochka\JsonRpcClient\Contracts\ResultMapperInterface;
use Tochka\JsonRpcClient\Contracts\RouterInterface;
use Tochka\JsonRpcClient\Contracts\TransportClientInterface;
use Tochka\JsonRpcClient\Support\ClientConfig;
use Tochka\JsonRpcClient\Support\ClientsConfig;
use Tochka\JsonRpcClient\Support\MiddlewareRegistry;
use Tochka\JsonRpcClient\Support\ParametersPreparer;
use Tochka\JsonRpcClient\Support\ResponseParser;
use Tochka\JsonRpcClient\Support\ResultMapper;
use Tochka\JsonRpcClient\Support\Router;
use Tochka\JsonRpcClient\Support\TransportClient;

/**
 * @psalm-api
 * @psalm-import-type ClientConfigArray from ClientConfig
 */
class JsonRpcClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TransportClientInterface::class, function (): TransportClient {
            return new TransportClient(
                HttpClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
            );
        });

        $this->app->singleton(ResponseParserInterface::class, ResponseParser::class);
        $this->app->singleton(ResultMapperInterface::class, ResultMapper::class);
        $this->app->singleton(RouterInterface::class, Router::class);
        $this->app->singleton(ParametersPreparerInterface::class, ParametersPreparer::class);

        /** @var array<string, ClientConfigArray> $servers */
        $connections = Config::get('jsonrpc-client.connections', []);
        $clientName = Config::get('jsonrpc-client.clientName', 'default');

        $config = new ClientsConfig($connections, $clientName);
        $this->app->instance(ClientsConfig::class, $config);

        foreach ($config->clientsConfig as $clientConfig) {
            $this->app->when($clientConfig->clientClass)
                ->needs(ClientConfig::class)
                ->give(fn () => $clientConfig);
        }

        $this->app->singleton(
            ClientMiddlewareRegistryInterface::class,
            function (Container $container) use ($config) {
                $registry = $container->make(MiddlewareRegistry::class);
                foreach ($config->clientsConfig as $clientName => $clientConfig) {
                    $registry->addMiddlewaresFromConfig($clientName, $clientConfig->middleware);
                }

                return $registry;
            }
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    //GenerateClient::class,
                ]
            );
        }

        $this->publishes(
            [__DIR__ . '/../config/jsonrpc-client.php' => $this->app->configPath('config/jsonrpc-client.php')],
            'jsonrpc-client-config'
        );
    }
}
