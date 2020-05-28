<?php

namespace Tochka\JsonRpcClient\DocBlock;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Reflection class for the {@}apiObject tag in a Docblock.
 */
class Method extends BaseTag implements StaticMethod
{
    protected const REGEXP_METHOD = /** @lang text */
        '/((?<isStatic>static)? +)?(?<type>([a-z\[\]\_]+)[ ]+)?(?<methodName>[a-z0-9\_]+)\((?<arguments>[^\)]*)\)[ \n]*(?<description>.+)?/is';
    protected const REGEXP_ARGUMENT = /** @lang text */
        '/(?<type>(\??[\\a-z\[\]\_|]+)[ ]+)?\$(?<argumentName>[a-z0-9\_]+)(\s*=\s*)?(?<default>(\S*))/is';
    protected const TAG_NAME = 'method';

    /** @var string */
    protected $methodName = '';

    /** @var string[] */
    protected $arguments = [];

    /** @var bool */
    protected $isStatic = false;

    /** @var Type */
    protected $returnType;

    public function __construct(string $methodName,
                                array $arguments = [],
                                Type $returnType = null,
                                bool $static = false,
                                Description $description = null)
    {
        Assert::stringNotEmpty($methodName);

        $this->name = self::TAG_NAME;
        $this->methodName = $methodName;
        $this->arguments = $arguments;
        $this->returnType = $returnType;
        $this->isStatic = $static;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    )
    {
        Assert::stringNotEmpty($body);
        Assert::allNotNull([$typeResolver, $descriptionFactory]);

        if (!preg_match(self::REGEXP_METHOD, $body, $parts, PREG_UNMATCHED_AS_NULL)) {
            return null;
        }

        $description = null;

        if (null !== $descriptionFactory) {
            $descriptionStr = isset($parts['description']) ? trim($parts['description']) : '';
            $description = $descriptionFactory->create($descriptionStr, $context);
        }

        $arguments = [];
        if (!empty($parts['arguments'])) {
            $argumentArr = explode(',', $parts['arguments']);
            foreach ($argumentArr as $arg) {
                $arg = trim($arg);

                if (!preg_match(self::REGEXP_ARGUMENT, $arg, $matches, PREG_UNMATCHED_AS_NULL)) {
                    continue;
                }

                $argument = [
                    'name' => $matches['argumentName'],
                ];

                // if T $value = null, make T|null $value
                if (($matches['default'] ?? null) === 'null' && $matches['type'] ?? false) {
                    if (strpos($matches['type'], '?') === false && strpos($matches['type'], 'null') === false) {
                        $matches['type'] = trim($matches['type']) . '|null';
                    }
                }

                /** @noinspection NullPointerExceptionInspection */
                $type = $typeResolver->resolve($matches['type'] ?? 'mixed', $context);
                if ($type) {
                    $argument['type'] = $type;
                }

                $arguments[] = $argument;
            }
        }

        /** @noinspection NullPointerExceptionInspection */
        $returnType = $typeResolver->resolve($parts['type'] ?? 'mixed', $context);

        return new static($parts['methodName'], $arguments, $returnType, !empty($parts['isStatic']), $description);
    }

    /**
     * Retrieves the method name.
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Checks whether the method tag describes a static method or not.
     *
     * @return bool TRUE if the method declaration is for a static method, FALSE otherwise.
     */
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * @return Type
     */
    public function getReturnType(): string
    {
        return $this->returnType;
    }

    public function __toString() : string
    {
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument['type'] . ' $' . $argument['name'];
        }

        return trim(($this->isStatic() ? 'static ' : '')
            . $this->returnType . ' '
            . $this->methodName
            . '(' . implode(', ', $arguments) . ')'
            . ($this->description ? ' ' . $this->description->render() : ''));
    }

}
