<?php

declare(strict_types=1);

namespace Kaa\Validator\InterceptorGenerator;

use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Exception\InvalidArgumentException;
use Kaa\Validator\Exception\UnsupportedAssertException;
use Kaa\Validator\Exception\ValidatorReturnValueException;
use Kaa\Validator\GeneratorContext;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;

class ValidatorReturnValueGenerator implements InterceptorGeneratorInterface
{
    public function __construct(
        private ValidatorModelGenerator $validatorModelGenerator,
    ) {
    }

    /**
     * @throws ValidatorReturnValueException
     * @throws \ReflectionException
     * @throws UnsupportedAssertException
     * @throws InvalidArgumentException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        InterceptorUtils::checkValidClass($action);

        $validateResultType = $action->reflectionMethod->getReturnType();

        if (is_a($validateResultType->getName(), ResponseInterface::class)) {
            return '';
        }

        $varToValidate = $availableVars->getFirstByType(
            $validateResultType->getName(),
        ) ?? throw new ValidatorReturnValueException(
            sprintf(
                'Var with type %s is not available',
                $validateResultType->getName(),
            )
        );

        $generatedCode = $this->validatorModelGenerator->generate($varToValidate);

        array_unshift($generatedCode, '$postViolationList = [];');
        $availableVars->add(new AvailableVar('postViolationList', 'array'));

        $throwCode = <<<'PHP'
if (!empty($postViolationList)) {
    throw new \Kaa\Validator\ValidationException($postViolationList);
}
PHP;
        $generatedCode[] = $throwCode;

        return implode("\n", $generatedCode);
    }
}
