<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\LessThan;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
class LessThanGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof LessThan;
    }

    /**
     * @param LessThan $assert
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
        $code = <<<'PHP'
if (%s >= %s){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message;
        $message = preg_replace('/{{ compared_value }}/', (string)$assert->value, $message);
        $code = sprintf(
            $code,
            $accessCode,
            $assert->value,
            $violationListVarName,
            $modelVar->type,
            $reflectionProperty->name,
            $message
        );
        return [$code];
    }
}
