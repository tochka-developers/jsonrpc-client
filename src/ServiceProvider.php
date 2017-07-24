<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClientGenerator::class
            ]);
        }
    }
}