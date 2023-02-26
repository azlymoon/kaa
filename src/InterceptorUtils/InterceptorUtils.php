<?php

declare(strict_types=1);

namespace Kaa\InterceptorUtils;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\Exception\ParamNotFoundException;
use Kaa\Router\Action;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

#[PhpOnly]
class InterceptorUtils
{
    /**
     * Возвращает тип параметра метода из Action по имени этого параметра
     *
     * @throws ParamNotFoundException Если у метода нет параметра с таким именем или тип параметра является
     * объединением или пересечением
     */
    public static function getParameterTypeByName(Action $action, string $parameterName): string
    {
        foreach ($action->reflectionMethod->getParameters() as $parameter) {
            if ($parameter->name !== $parameterName) {
                continue;
            }

            $paramType = $parameter->getType();
            if (!$paramType instanceof ReflectionNamedType) {
                throw new ParamNotFoundException(
                    sprintf(
                        "Parameter %s of %s::%s must have type and must not be union or intersection",
                        $parameter->name,
                        $action->reflectionClass->name,
                        $action->reflectionMethod->name,
                    )
                );
            }

            return $paramType->getName();
        }

        throw new ParamNotFoundException(
            sprintf(
                "%s::%s doest not have parameter with name %s",
                $action->reflectionClass->name,
                $action->reflectionMethod->name,
                $parameterName,
            )
        );
    }

    /**
     * Генерирует строчку кода, которая устанавливает свойству $reflectionProperty объекта с именем $objectName
     * значение $value
     *
     * @param string $value Может быть строкой с константой, вызовом метода, конструктора и т.д.
     * @throws InaccessiblePropertyException
     * @throws ReflectionException
     */
    public static function generateSetStatement(
        ReflectionProperty $reflectionProperty,
        string $objectName,
        string $value
    ): string {
        if ($reflectionProperty->isPublic()) {
            return sprintf('$%s->%s = %s;', $objectName, $reflectionProperty->name, $value);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $setterMethodName = self::getMethodNameWithRightCase(
            $reflectionClass,
            'set' . $reflectionProperty->name
        );
        if ($setterMethodName === null) {
            throw new InaccessiblePropertyException(
                sprintf(
                    'Property %s::%s is private and it`s class does not have method %s',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                    $setterMethodName,
                )
            );
        }

        if (!$reflectionClass->getMethod($setterMethodName)->isPublic()) {
            throw new InaccessiblePropertyException(
                sprintf(
                    'Property %s::%s is private and it`s setter %s is also private',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                    $reflectionProperty->name,
                )
            );
        }

        return sprintf("$%s->%s(%s);", $objectName, $setterMethodName, $value);
    }

    /**
     * Генерирует код, который получает значение свойства $reflectionProperty объекта
     * с именем $objectName
     *
     * @return string Код, который может быть использован с правой части от оператора присваивания
     * или передан в качестве аргумента при вызове метода
     *
     * @throws InaccessiblePropertyException
     * @throws ReflectionException
     */
    public static function generateGetCode(
        ReflectionProperty $reflectionProperty,
        string $objectName,
    ): string {
        if ($reflectionProperty->isPublic()) {
            return sprintf('$%s->%s', $objectName, $reflectionProperty->name);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();

        $getterNames = [
            self::getMethodNameWithRightCase($reflectionClass, 'get' . $reflectionProperty->name),
            self::getMethodNameWithRightCase($reflectionClass, 'is' . $reflectionProperty->name)
        ];

        $getterNames = array_filter($getterNames);

        foreach ($getterNames as $getterName) {
            if ($reflectionClass->getMethod($getterName)->isPublic()) {
                return sprintf('$%s->%s()', $objectName, $getterName);
            }
        }

        throw new InaccessiblePropertyException(
            sprintf(
                "Property %s::%s is private and has no public getters",
                $reflectionClass->name,
                $reflectionProperty->name
            )
        );
    }

    private static function getMethodNameWithRightCase(ReflectionClass $reflectionClass, string $methodName): ?string
    {
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (strcasecmp($reflectionMethod->name, $methodName) === 0) {
                return $reflectionMethod->name;
            }
        }

        return null;
    }
}
