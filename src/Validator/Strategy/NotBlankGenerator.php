<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\NotBlank;
use ReflectionProperty;

#[PhpOnly]
class NotBlankGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof NotBlank;
    }

    /**
     * @param NotBlank $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName,
    ): array {
        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $modelVar->name);

        $code = <<<'PHP'
if (empty(%s) && '0' !== %s) {
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message ?? 'This value should not be blank.';
        $code = sprintf(
            $code,
            $accessCode,
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
