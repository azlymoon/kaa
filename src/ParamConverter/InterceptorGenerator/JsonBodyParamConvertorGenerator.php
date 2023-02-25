<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Request;
use Kaa\InterceptorUtils\Exception\ParamNotFoundException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\ParamConverter\Exception\ParamConverterException;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;

#[PhpOnly]
readonly class JsonBodyParamConvertorGenerator implements InterceptorGeneratorInterface
{
    public function __construct(
        private string $modelName,
    ) {
    }

    /**
     * @throws ParamConverterException
     * @throws ParamNotFoundException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $requestVar = $availableVars->getFirstByType(Request::class)
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

        $generatedCode = sprintf(
            "$%s = \JsonEncoder::decode($%s->getPhpInput(), \%s::class);",
            $this->modelName,
            $requestVar->name,
            $modelClass,
        );

        $availableVars->add(new AvailableVar($this->modelName, $modelClass));

        return $generatedCode;
    }
}
