<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\ParamConverter\Exception\ParamConverterException;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use ReflectionNamedType;

#[PhpOnly]
readonly class ResponseToJsonConverterGenerator implements InterceptorGeneratorInterface
{
    /**
     * @throws ParamConverterException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $controllerResultType = $action->reflectionMethod->getReturnType();
        if (!$controllerResultType instanceof ReflectionNamedType) {
            throw new ParamConverterException(
                sprintf(
                    '%s::%s must have return type and it must not be union or intersection',
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );
        }

        if ($controllerResultType->isBuiltin()) {
            throw new ParamConverterException(
                sprintf(
                    "Return type of %s::%s must not be build in, but is %s",
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                    $controllerResultType->getName(),
                )
            );
        }

        if (is_subclass_of($controllerResultType->getName(), ResponseInterface::class)) {
            return '';
        }

        $controllerResultVar = $availableVars->getFirstByType(
            $controllerResultType->getName(),
        ) ?? throw new ParamConverterException(
            sprintf(
                'Var with type %s is not available',
                $controllerResultType->getName(),
            )
        );

        $availableVars->add(new AvailableVar('ParamConverter_result', ResponseInterface::class));

        return sprintf(
            '$ParamConverter_result = \Kaa\HttpKernel\Response\JsonResponse::fromObject($%s);',
            $controllerResultVar->name,
        );
    }
}
