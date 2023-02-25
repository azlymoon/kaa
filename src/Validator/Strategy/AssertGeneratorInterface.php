<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use ReflectionProperty;

#[PhpOnly]
interface AssertGeneratorInterface
{
    /**
     * Поддерживается ли кодогенерация для переданного аттрибута
     */
    public function supports(Assert $assert): bool;

    /**
     * @return string[]
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array;
}
