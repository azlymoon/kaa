<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Request;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\Exception\ParamNotFoundException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\ParamConverter\Exception\ParamConverterException;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

#[PhpOnly]
abstract readonly class AbstractSetterParamConverterGenerator implements InterceptorGeneratorInterface
{
    public function __construct(
        private string $modelName,
    ) {
    }

    /**
     * @throws ParamConverterException
     * @throws ParamNotFoundException
     * @throws ReflectionException
     * @throws InaccessiblePropertyException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $requestVarName = $availableVars->getFirstByType(Request::class)?->name
            ?? throw new ParamConverterException(
                sprintf(
                    "Var of type %s is not available to param converter %s on %s::%s",
                    Request::class,
                    self::class,
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );

        $modelClass = InterceptorUtils::getParameterTypeByName($action, $this->modelName);
        $reflectionClass = new ReflectionClass($modelClass);

        $constructorArguments = $this->getConstructorArguments($requestVarName, $reflectionClass);
        $constructorCallCode = sprintf(
            "$%s = new \%s(%s);",
            $this->modelName,
            $modelClass,
            implode(' ,', $constructorArguments),
        );

        $generatedCode = [$constructorCallCode];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if (array_key_exists($reflectionProperty->name, $constructorArguments)) {
                continue;
            }

            $generatedCode[] = InterceptorUtils::generateSetStatement(
                $reflectionProperty,
                $this->modelName,
                $this->generateGetCodeForProperty($reflectionProperty, $requestVarName)
            );
        }

        $availableVars->add(new AvailableVar($this->modelName, $modelClass));

        return implode("\n", $generatedCode);
    }

    /**
     * @return string[]
     * @throws ParamConverterException
     */
    private function getConstructorArguments(string $requestVarName, ReflectionClass $reflectionClass): array
    {
        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $constructorArguments = [];
        foreach ($constructor->getParameters() as $reflectionParameter) {
            $constructorArguments[$reflectionParameter->name] = $this->generateGetCodeForParameter(
                $reflectionParameter,
                $requestVarName,
            );
        }

        return $constructorArguments;
    }

    /**
     * @throws ParamConverterException
     */
    private function generateGetCodeForParameter(
        ReflectionParameter $reflectionParameter,
        string $requestVarName,
    ): string {
        /** @phpstan-ignore-next-line */
        $className = $reflectionParameter->getDeclaringClass()->name;
        $methodName = $reflectionParameter->getDeclaringFunction()->name;

        $type = $reflectionParameter->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new ParamConverterException(
                sprintf(
                    "Parameter %s of %s::%s must have type and must not be union or intersection",
                    $reflectionParameter->name,
                    $className,
                    $methodName,
                )
            );
        }

        if (!$type->isBuiltin()) {
            throw new ParamConverterException(
                sprintf(
                    "To be used in SimpleParamConverter parameter %s of %s::%s"
                    . "must have built in type",
                    $reflectionParameter->name,
                    $className,
                    $methodName,
                )
            );
        }

        return $this->generateGetCode($reflectionParameter->name, $type->getName(), $requestVarName);
    }

    private function generateGetCode(string $parameterName, string $typeName, string $requestVarName): string
    {
        if ($typeName !== 'array') {
            return sprintf(
                "(%s) $%s->%s('%s')",
                $typeName,
                $requestVarName,
                $this->getGetParameterMethodName(),
                $parameterName,
            );
        }

        return sprintf(
            "$%s->%s('%s')",
            $requestVarName,
            $this->getGetParameterMethodName(),
            $parameterName,
        );
    }

    abstract protected function getGetParameterMethodName(): string;

    /**
     * @throws ParamConverterException
     */
    private function generateGetCodeForProperty(
        ReflectionProperty $reflectionProperty,
        string $requestVarName,
    ): string {
        $className = $reflectionProperty->getDeclaringClass()->name;

        $type = $reflectionProperty->getType();
        if (!$type instanceof ReflectionNamedType) {
            throw new ParamConverterException(
                sprintf(
                    "Property %s::%s must have type and must not be union or intersection",
                    $className,
                    $reflectionProperty->name,
                )
            );
        }

        if (!$type->isBuiltin()) {
            throw new ParamConverterException(
                sprintf(
                    "To be used in SimpleParamConverter property %s::%s"
                    . "must have built in type",
                    $className,
                    $reflectionProperty->name,
                )
            );
        }

        return $this->generateGetCode($reflectionProperty->name, $type->getName(), $requestVarName);
    }
}
