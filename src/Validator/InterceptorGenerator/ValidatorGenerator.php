<?php

declare(strict_types=1);

namespace Kaa\Validator\InterceptorGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Action;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Router\Interceptor\AvailableVars;
use Kaa\Router\Interceptor\InterceptorGeneratorInterface;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Exception\InvalidArgumentException;
use Kaa\Validator\Exception\UnsupportedAssertException;
use Kaa\Validator\Exception\VarToValidateNotFoundException;
use Kaa\Validator\GeneratorContext;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

#[PhpOnly]
readonly class ValidatorGenerator implements InterceptorGeneratorInterface
{
    public function __construct(
        private string $modelName,
        private ValidatorModelGenerator $validatorModelGenerator,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws VarToValidateNotFoundException
     * @throws UnsupportedAssertException
     * @throws InvalidArgumentException
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string {
        $varToValidate = $availableVars->getFirstByName($this->modelName)
            ?? throw new VarToValidateNotFoundException(
                sprintf(
                    'None of the previous interceptors for %s::%s generated variable %s',
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                    $this->modelName,
                )
            );

        $generatedCode = $this->validatorModelGenerator->generate($varToValidate);

        array_unshift($generatedCode, '$violationList = [];');
        $availableVars->add(new AvailableVar('violationList', 'array'));

        return implode("\n", $generatedCode);
    }
}
