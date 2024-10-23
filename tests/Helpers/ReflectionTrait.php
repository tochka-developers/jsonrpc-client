<?php

namespace Tochka\JsonRpcClient\Tests\Helpers;

trait ReflectionTrait
{
    /**
     * Получить приватную или протектную проперти
     *
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function getProperty($obj, string $attribute)
    {
        $reflectionObj = new \ReflectionObject($obj);
        $property = $reflectionObj->getProperty($attribute);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    /**
     * Установить приватную или протектную проперти
     *
     *
     * @throws \ReflectionException
     */
    public function setProperty($obj, string $attribute, $value)
    {
        $reflectionObj = new \ReflectionObject($obj);
        $property = $reflectionObj->getProperty($attribute);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

    /**
     * Вызвать приватаный или протектед метод
     *
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function callMethod($obj, string $method, array $attributes = [])
    {
        $reflectionObj = new \ReflectionObject($obj);
        $method = $reflectionObj->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $attributes);
    }

    /**
     * Вызвать приватаный или протектед метод статический метод
     *
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function callStaticMethod($class, $method, array $attributes = [])
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs(null, $attributes);
    }
}
