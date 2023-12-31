<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\GreaterThan;
use Kaa\Validator\Assert\Positive;
use ReflectionProperty;

#[PhpOnly]
class PositiveGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof Positive;
    }

    /**
     * @param Positive $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        return (new GreaterThanGenerator())->generateAssert(
            new GreaterThan(
                value: 0,
                message: $assert->message,
            ),
            $reflectionProperty,
            $modelVar,
            $violationListVarName,
        );
    }
}
