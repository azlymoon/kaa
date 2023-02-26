<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\LessThanOrEqual;
use Kaa\Validator\Assert\NegativeOrZero;
use ReflectionProperty;

#[PhpOnly]
class NegativeOrZeroGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof NegativeOrZero;
    }

    /**
     * @param NegativeOrZero $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        return (new LessThanOrEqualGenerator())->generateAssert(
            new LessThanOrEqual(
                value: 0,
                message: $assert->message ?? 'This value should be negative or zero.',
            ),
            $reflectionProperty,
            $modelVar,
            $violationListVarName,
        );
    }
}
