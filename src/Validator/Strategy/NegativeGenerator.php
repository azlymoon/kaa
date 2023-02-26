<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\LessThan;
use Kaa\Validator\Assert\Negative;
use ReflectionProperty;

#[PhpOnly]
class NegativeGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof Negative;
    }

    /**
     * @param Negative $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        return (new LessThanGenerator())->generateAssert(
            new LessThan(
                value: 0,
                message: $assert->message ?? 'This value should be negative.',
            ),
            $reflectionProperty,
            $modelVar,
            $violationListVarName,
        );
    }
}
