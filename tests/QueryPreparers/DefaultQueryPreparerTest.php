<?php

namespace Tochka\JsonRpcClient\Tests\QueryPreparers;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\DocBlock\Method;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;
use Tochka\JsonRpcClient\Tests\Helpers\ReflectionTrait;
use Tochka\JsonRpcClient\Tests\QueryPreparers\TestClients\TestClientClass;

class DefaultQueryPreparerTest extends TestCase
{
    use ReflectionTrait;


    /**
     * @return \Tochka\JsonRpcClient\ClientConfig
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    protected function makeConfig(): ClientConfig
    {
        return new ClientConfig('name', 'service', [
            'clientClass' => TestClientClass::class,
            'url'         => 'url',
        ]);
    }

    public function providerCastCompoundTypeTo()
    {
        return [
            'type int got int'             => [1, $this->wrapType(new Integer()), false],
            'type string got string'       => ['1', $this->wrapType(new String_()), false],
            'type null got null'           => [null, $this->wrapType(new Null_()), false],
            'type bool got bool'           => [true, $this->wrapType(new Boolean()), false],
            'type float got float'         => [0.5, $this->wrapType(new Float_()), false],
            'type object got object'       => [new \stdClass(), $this->wrapType(new Object_()), false],
            'type array got array'         => [[], $this->wrapType(new Array_()), false],
            'type \stdClass got \stdClass' => [new \stdClass(), $this->wrapType(new Object_()), false],
            // compound
            'type int|null got int'        => [1, $this->wrapType(new Compound([new Integer(), new Null_()])), false],
            'type int|null got null'       => [
                null,
                $this->wrapType(new Compound([new Integer(), new Null_()])),
                false,
            ],
            'type string|null got string'  => ['1', $this->wrapType(new Compound([new String_(), new Null_()])), false],
            'type string|null got null'    => [
                null,
                $this->wrapType(new Compound([new String_(), new Null_()])),
                false,
            ],
            'type array|\\stdClass got array' => [[], $this->wrapType(new Compound([new Array_(), new Object_()])), false],
            // errors
            'type int|null got string'     => ['1', $this->wrapType(new Compound([new Integer(), new Null_()])), true],
            'type int|float got string'    => ['1', $this->wrapType(new Compound([new Integer(), new Float_()])), true],
            'type int got string'          => ['1', $this->wrapType(new Integer()), true],
            'type object got array'        => [[], $this->wrapType(new Object_()), true],
            'type array got object'        => [new \stdClass(), $this->wrapType(new Array_()), true],
            'type int|bool|null got array' => [
                [],
                $this->wrapType(new Compound([new Integer(), new Boolean(), new Null_()])),
                true,
            ],
            // nullable
            'type nullable'                => [1, $this->wrapType(new Nullable(new Integer())), false],
            'type nullable null'           => [null, $this->wrapType(new Nullable(new Integer())), false],
            'type nullable bad type'       => ['string', $this->wrapType(new Nullable(new Integer())), true],
            'mixed'                        => ['chot', $this->wrapType(new Mixed_()), false],
        ];
    }

    protected function wrapType($type)
    {
        return ['name' => 'name', 'type' => $type];
    }

    /**
     * @dataProvider providerCastCompoundTypeTo
     *
     * @param      $value
     * @param      $type
     * @param bool $expectException
     *
     * @throws \ReflectionException
     */
    public function testCheckType($value, $type, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(JsonRpcClientException::class);
        }
        $preparer = new DefaultQueryPreparer();
        $this->callMethod($preparer, 'checkType', [$value, $type, 'method']);
        $this->assertTrue(true);
    }

    /**
     * @throws \ReflectionException
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    public function testMapMethodsClassNotFound()
    {
        $this->expectException(JsonRpcClientException::class);
        $preparer = new DefaultQueryPreparer();
        $this->callMethod($preparer, 'mapMethods', [
            new ClientConfig('name', 'service', [
                'clientClass' => '',
                'url'         => 'url',
            ]),
        ]);
    }

    public function providerMapMethods()
    {
        return [
            'name_intMethod'           => ['name_intMethod', ['int']],
            'name_stringMethod'        => ['name_stringMethod', ['string']],
            'name_intOrNull'           => ['name_intOrNull', ['int|null']],
            'name_intAndBoolAndString' => ['name_intAndBoolAndString', ['int', 'bool', 'string']],
            'name_object'              => ['name_object', ['object']],
            'name_stdClass'            => ['name_stdClass', ['\stdClass']],
            'name_arrayOrStdClass'     => ['name_arrayOrStdClass', ['array|\stdClass']],
        ];
    }

    /**
     * @dataProvider providerMapMethods
     *
     * @param string $methodName
     * @param array  $types
     *
     * @throws \ReflectionException
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     * @covers \Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer::mapMethods
     */
    public function testMapMethods(string $methodName, array $types)
    {
        $preparer = new DefaultQueryPreparer();
        $this->callMethod($preparer, 'mapMethods', [$this->makeConfig()]);
        /** @var Method $resultMethod */
        $resultMethod = $this->getProperty($preparer, 'methods')[$methodName];
        $resultAttributes = $resultMethod->getArguments();
        for ($i = 0; $i < count($resultAttributes); $i++) {
            $this->assertEquals($types[$i], (string) $resultAttributes[$i]['type']);
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     * @covers \Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer::prepare()
     */
    public function testPrepareMethodNotFound()
    {
        $this->expectException(JsonRpcClientException::class);
        $preparer = new DefaultQueryPreparer();
        $this->setProperty($preparer, 'methods', ['one' => '']);
        $preparer->prepare('name', [], $this->makeConfig());
    }

    /**
     * @throws \ReflectionException
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     * @covers \Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer::prepare()
     */
    public function testPrepare()
    {
        $preparer = new DefaultQueryPreparer();
        $this->setProperty($preparer, 'methods', [
            'one' => new Method('one', [
                ['name' => 'first', 'type' => new Integer()],
                ['name' => 'second', 'type' => new String_()],
            ]),
        ]);
        $jsonRpcRequest = $preparer->prepare('one', [5, '6'], $this->makeConfig());
        $this->assertNotNull($jsonRpcRequest->id);
        $this->assertSame('one', $jsonRpcRequest->method);
        $this->assertSame(['first' => 5, 'second' => '6'], $jsonRpcRequest->params);
    }

    /**
     * @throws \ReflectionException
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     * @covers \Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer::prepare()
     */
    public function testPrepareMapMethodsIfNotMapped()
    {
        $preparer = new DefaultQueryPreparer();
        $preparer->prepare('name_intMethod', [5, '6'], $this->makeConfig());
        $this->assertNotEmpty($this->getProperty($preparer, 'methods'));
    }
}
