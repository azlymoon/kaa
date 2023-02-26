<?php

declare(strict_types=1);

namespace Kaa\InterceptorUtils\Test;

use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\Exception\ParamNotFoundException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Action;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class InterceptorUtilsTest extends TestCase
{
    private ?Action $action;

    private ?ReflectionClass $modelReflectionClass;

    /**
     * @throws ParamNotFoundException
     */
    public function testGetParameterTypeByNameClassType(): void
    {
        $type = InterceptorUtils::getParameterTypeByName($this->action, 'model');
        $this->assertEquals(TestModel::class, $type);
    }

    /**
     * @throws ParamNotFoundException
     */
    public function testGetParameterTypeByNameNativeType(): void
    {
        $type = InterceptorUtils::getParameterTypeByName($this->action, 'string');
        $this->assertEquals('string', $type);
    }

    /**
     * @throws ParamNotFoundException
     */
    public function testGetParameterTypeByNameThrowsOnUnion(): void
    {
        $this->expectException(ParamNotFoundException::class);
        InterceptorUtils::getParameterTypeByName($this->action, 'union');
    }

    /**
     * @throws ParamNotFoundException
     */
    public function testGetParameterTypeByNameThrowsWhenNotFound(): void
    {
        $this->expectException(ParamNotFoundException::class);
        InterceptorUtils::getParameterTypeByName($this->action, 'notExisting');
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateSetStatementForPublicProperty(): void
    {
        $property = $this->modelReflectionClass->getProperty('public');
        $setCode = InterceptorUtils::generateSetStatement(
            $property,
            'test',
            '"test"'
        );

        $this->assertEquals('$test->public = "test";', $setCode);
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateSetStatementForPrivateProperty(): void
    {
        $property = $this->modelReflectionClass->getProperty('private');
        $setCode = InterceptorUtils::generateSetStatement(
            $property,
            'test',
            '"test"'
        );

        $this->assertEquals('$test->setPrivate("test");', $setCode);
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateSetStatementThrowsOnInaccessible(): void
    {
        $property = $this->modelReflectionClass->getProperty('veryPrivate');

        $this->expectException(InaccessiblePropertyException::class);
        InterceptorUtils::generateSetStatement(
            $property,
            'test',
            '"test"'
        );
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateGetCodeForPublicProperty(): void
    {
        $property = $this->modelReflectionClass->getProperty('public');
        $getCode = InterceptorUtils::generateGetCode(
            $property,
            'test',
        );

        $this->assertEquals('$test->public', $getCode);
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateGetCodeForPrivateProperty(): void
    {
        $property = $this->modelReflectionClass->getProperty('private');
        $setCode = InterceptorUtils::generateGetCode(
            $property,
            'test',
        );

        $this->assertEquals('$test->getPrivate()', $setCode);
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateGetCodeForPrivateBoolProperty(): void
    {
        $property = $this->modelReflectionClass->getProperty('privateBool');
        $setCode = InterceptorUtils::generateGetCode(
            $property,
            'test',
        );

        $this->assertEquals('$test->isPrivateBool()', $setCode);
    }

    /**
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function testGenerateGetCodeThrowsOnInaccessible(): void
    {
        $property = $this->modelReflectionClass->getProperty('veryPrivate');

        $this->expectException(InaccessiblePropertyException::class);
        InterceptorUtils::generateGetCode(
            $property,
            'test',
        );
    }

    protected function setUp(): void
    {
        $this->action = new Action(
            new ReflectionMethod(TestController::class, 'testMethod'),
            new ReflectionClass(TestController::class),
            [],
            [],
            [],
            [],
        );

        $this->modelReflectionClass = new ReflectionClass(TestModel::class);
    }

    protected function tearDown(): void
    {
        $this->action = null;
        $this->modelReflectionClass = null;
    }
}

class TestModel
{
    public string $public;

    private string $veryPrivate;

    private bool $privateBool;

    private string $private;

    public function isPrivateBool(): bool
    {
        return $this->privateBool;
    }

    public function setPrivateBool(bool $privateBool): void
    {
        $this->privateBool = $privateBool;
    }

    public function getPrivate(): string
    {
        return $this->private;
    }

    public function setPrivate(string $private): void
    {
        $this->private = $private;
    }
}

class TestController
{
    public function testMethod(TestModel $model, string $string, string|TestModel $union): void
    {
    }
}
