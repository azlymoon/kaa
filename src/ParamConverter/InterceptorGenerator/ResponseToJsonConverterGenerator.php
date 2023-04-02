<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\ParamConverter\Exception\ParamConverterException;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Validator\Exception\ValidatorReturnValueException;
use ReflectionNamedType;

#[PhpOnly]
readonly class ResponseToJsonConverterGenerator implements InterceptorGeneratorInterface
{
    /**
     * @throws ParamConverterException
     * @throws ValidatorReturnValueException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        InterceptorUtils::checkValidClass($action);

        $controllerResultType = $action->reflectionMethod->getReturnType();

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
