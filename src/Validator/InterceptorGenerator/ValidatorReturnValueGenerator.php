<?php

declare(strict_types=1);

namespace Kaa\Validator\InterceptorGenerator;

use Kaa\CodeGen\ProvidedDependencies;
use Kaa\HttpKernel\Response\ResponseInterface;
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
        private string $exceptionClass,
        private GeneratorContext $generatorContext,
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
        $validateResultType = $action->reflectionMethod->getReturnType();

        if (is_subclass_of($validateResultType->getName(), ResponseInterface::class)) {
            return '';
        }

        if ($validateResultType instanceof ReflectionNamedType) {
            throw new ValidatorReturnValueException(
                sprintf(
                    '%s::%s must have return type and it must not be union or intersection',
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                )
            );
        }

        if ($validateResultType->isBuiltin()) {
            throw new ValidatorReturnValueException(
                sprintf(
                    "Return type of %s::%s must not be build in, but is %s",
                    $action->reflectionClass->name,
                    $action->reflectionMethod->name,
                    $validateResultType->getName(),
                )
            );
        }

        $varToValidate = $availableVars->getFirstByType(
            $validateResultType->getName(),
        ) ?? throw new ValidatorReturnValueException(
            sprintf(
                'Var with type %s is not available',
                $validateResultType->getName(),
            )
        );

        $generatedCode = [];
        $reflectionClass = new ReflectionClass($varToValidate->type);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $assertAttributes = $reflectionProperty->getAttributes(
                Assert::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            foreach($assertAttributes as $assertAttribute) {
                $attribute = $assertAttribute->newInstance();

                if ($attribute->supportsType($reflectionProperty->getType()->getName()) === false){

                    $allowTypes = implode(", ", $attribute->getAllowTypes());

                    throw new InvalidArgumentException(
                        sprintf(
                            'Type of $%s is %s but should be %s.',
                            $reflectionProperty->getName(),
                            $reflectionProperty->getType()->getName(),
                            $allowTypes,
                        )
                    );
                }

                $generator = $this->generatorContext->getStrategy($attribute);
                if ($generator === null) {
                    throw new UnsupportedAssertException(
                        sprintf(
                            'Could not find strategy that supports %s',
                            $attribute::class,
                        )
                    );
                }

                $constraintGeneratedCode = $generator->generateAssert(
                    $attribute,
                    $reflectionProperty,
                    $varToValidate,
                    'violationList',
                );

                $generatedCode[] = $constraintGeneratedCode;
            }
        }
        $generatedCode = array_merge(...$generatedCode);
        array_unshift($generatedCode, '$violationList = [];');
        $availableVars->add(new AvailableVar('violationList', 'array'));

        $throwCode = <<<'PHP'
if (!empty($violationList) {
    throw new \%s($violationList);
}
PHP;
        $generatedCode[] = sprintf($throwCode, $this->exceptionClass);

        return implode("\n", $generatedCode);
    }
}