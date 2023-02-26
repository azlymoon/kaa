<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\GreaterThanOrEqual;
use ReflectionProperty;

#[PhpOnly]
class GreaterThanOrEqualGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof GreaterThanOrEqual;
    }

    /**
     * @param GreaterThanOrEqual $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $modelVar->name);

        $code = <<<'PHP'
if (null !== %s && %s < %s){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message ?? 'This value should be greater than or equal to {{ compared_value }}.';
        preg_replace('/{{ compared_value }}/', $assert->value, $message);
        $code = sprintf(
            $code,
            $accessCode,
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
