<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Blank;
use ReflectionProperty;

#[PhpOnly]
class BlankGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof Blank;
    }

    /**
     * @param Blank $assert
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
if ('' !== %s && null !== %s) {
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message ?? 'This value should be blank.';
        $code = sprintf(
            $code,
            $accessCode,
            $accessCode,
            $violationListVarName,
            $modelVar->type,
            $reflectionProperty->name,
            $message
        );

        return [$code];
    }
}
