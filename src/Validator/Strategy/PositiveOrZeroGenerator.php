<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\GreaterThanOrEqual;
use Kaa\Validator\Assert\PositiveOrZero;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
class PositiveOrZeroGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof PositiveOrZero;
    }

    /**
     * @param PositiveOrZero $assert
     * @throws InaccessiblePropertyException
     * @throws ReflectionException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName,
        string $accessCode,
    ): array {
        return (new GreaterThanOrEqualGenerator())->generateAssert(
            new GreaterThanOrEqual(
                value: 0,
                message: $assert->message ?? 'This value should be positive or zero.',
            ),
            $reflectionProperty,
            $modelVar,
            $violationListVarName,
            $accessCode,
        );
    }
}
