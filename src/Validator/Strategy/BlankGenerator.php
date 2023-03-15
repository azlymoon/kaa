<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Blank;
use ReflectionException;
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
     * @throws ReflectionException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $modelVar->name);

        if ($reflectionProperty->getType()->allowsNull()) {
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
        } else {
            $code = <<<'PHP'
if ('' !== %s) {
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
            $message = $assert->message ?? 'This value should be blank.';
            $code = sprintf(
                $code,
                $accessCode,
                $violationListVarName,
                $modelVar->type,
                $reflectionProperty->name,
                $message
            );
        }

        return [$code];
    }
}
