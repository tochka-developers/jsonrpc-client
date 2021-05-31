<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

use GuzzleHttp\Client;
use RuntimeException;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\HttpClient;
use Tochka\JsonRpcSmd\SmdDescription;

class ClientGenerator
{
    protected $config;
    protected $className;
    protected $classNamespace;
    protected $classFilePath;
    /** @var SmdDescription */
    public $smd;

    public function __construct(ClientConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @throws JsonRpcClientException
     */
    public function generate(): void
    {
        $client = new Client([
            'url' => $this->getUri(),
        ]);
        $result = $client->get($this->getUri());

        $smd = json_decode($result, true);

        // ошибка декодирования Json
        if ($smd === null) {
            throw new JsonRpcClientException(JsonRpcClientException::CODE_RESPONSE_PARSE_ERROR);
        }

        $this->getClassInfo();

        $this->smd = SmdDescription::fromArray($smd);
        $classClient = new ClientClass($this->smd, $this->config, $this->className, $this->classNamespace);

        $this->clearNamespace($classClient->getFullClassName());
        $classPath = $this->getClassPath($this->classNamespace, $this->className);
        file_put_contents($classPath, $classClient);

        if ($this->config->extendedStubs) {
            $subClasses = $classClient->getSubClasses();
            foreach ($subClasses as $subClass) {
                if ($subClass instanceof Stub) {
                    $classPath = $this->getClassPath($subClass->classNamespace, $subClass->className);
                    file_put_contents($classPath, $subClass);
                }
            }
        }
    }

    protected function getUri(): string
    {
        return $this->config->url . '?smd';
    }

    /**
     * Возвращает папку, в которой должен располагаться класс по его namespace
     *
     * @param string $namespace
     *
     * @return bool|string
     */
    private function getNamespaceDirectory($namespace)
    {
        $composerNamespaces = $this->getDefinedNamespaces();

        $namespaceFragments = explode('\\', trim($namespace, '\\'));
        $undefinedNamespaceFragments = [];

        while ($namespaceFragments) {
            $possibleNamespace = implode('\\', $namespaceFragments) . '\\';

            if (array_key_exists($possibleNamespace, $composerNamespaces)) {
                $path = app()->basePath() . DIRECTORY_SEPARATOR . $composerNamespaces[$possibleNamespace] . implode('/', array_reverse($undefinedNamespaceFragments));

                return $this->getAbsolutePath($path);
            }

            $undefinedNamespaceFragments[] = array_pop($namespaceFragments);
        }

        return false;
    }

    /**
     * Возвращает список объявленных namespace
     * @return array
     */
    private function getDefinedNamespaces(): array
    {
        $composerJsonPath = app()->basePath() . DIRECTORY_SEPARATOR . 'composer.json';
        $composerConfig = json_decode(file_get_contents($composerJsonPath));

        return (array)$composerConfig->autoload->{'psr-4'};
    }

    /**
     * Возвращает информацию о классе
     */
    protected function getClassInfo(): void
    {
        if (empty($this->config->clientClass)) {
            throw new RuntimeException('The class name for the client is not specified. Specify in the settings parameter "clientClass".');
        }
        $class = explode('\\', $this->config->clientClass);

        $this->className = array_pop($class);
        $this->classNamespace = trim(implode('\\', $class), '\\');
    }

    protected function getClassPath($classNamespace, $className): string
    {
        $directory = $this->getNamespaceDirectory($classNamespace);

        if ($directory === false) {
            throw new RuntimeException('Specified namespace not found.');
        }

        if (!file_exists($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Can not create folder "' . $directory . '" to save class.');
        }

        return $directory . DIRECTORY_SEPARATOR . $className . '.php';
    }

    protected function clearNamespace(string $namespace): void
    {
        $directory = $this->getNamespaceDirectory($namespace);

        if (file_exists($directory)) {
            $this->deleteDirectory($directory);
        }
    }

    private function deleteDirectory(string $directory): void
    {
        $files = glob($directory . '/*');

        foreach ($files as $file) {
            if (\is_file($file)) {
                unlink($file);
            } elseif (\is_dir($file)) {
                $this->deleteDirectory($file);
            }
        }

        rmdir($directory);
    }

    private function getAbsolutePath(string $path): string
    {
        $firstSlash = strpos($path, DIRECTORY_SEPARATOR) === 0;

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), '\strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return ($firstSlash ? DIRECTORY_SEPARATOR : '') . implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
