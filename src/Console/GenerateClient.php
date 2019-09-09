<?php /** @noinspection ClassOverridesFieldOfSuperClassInspection */

namespace Tochka\JsonRpcClient\Console;

use Illuminate\Console\Command;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\ClientGenerator\ClientGenerator;

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

        $services = config('jsonrpc-client.connections', []);
        $clientName = config('jsonrpc-client.clientName', []);
        $config = new ClientConfig($clientName, $connection, $services[$connection] ?? []);

        (new ClientGenerator($config))->generate();
        $this->info('Success!');
    }
}