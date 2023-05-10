<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\LessThan;
use Kaa\Validator\Assert\Negative;
use ReflectionException;
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
     * @throws ReflectionException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName,
        string $accessCode,
    ): array {
        return (new LessThanGenerator())->generateAssert(
            new LessThan(
                value: 0,
                message: $assert->message,
            ),
            $reflectionProperty,
            $modelVar,
            $violationListVarName,
            $accessCode,
        );
    }
}
