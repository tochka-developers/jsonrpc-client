<?php /** @noinspection ClassOverridesFieldOfSuperClassInspection */

namespace Tochka\JsonRpcClient\Console;

use Illuminate\Console\Command;
use Tochka\JsonRpcClient\ClientGenerator\ClientGenerator;
use Tochka\JsonRpcClient\Config;

class GenerateClient extends Command
{
    protected $signature = 'jsonrpc:generateClient {connection?}';

    protected $description = 'Generate proxy-client for JsonRpc server by SMD-scheme';

    /**
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    public function handle(): void
    {
        $connection = $this->argument('connection');

        if ($connection === null) {
            $connections = config('jsonrpc-client.connections');
            foreach ($connections as $key => $connection) {
                $this->generate($key);
            }
        } else {
            $this->generate($connection);
        }
    }

    /**
     * @param string $connection
     *
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    protected function generate(string $connection): void
    {
        $this->info('Generate client class for connection: ' . $connection);
        (new ClientGenerator(Config::create($connection)))->generate();
        $this->info('Success!');
    }
}