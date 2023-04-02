<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Range;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
class RangeGenerator implements AssertGeneratorInterface
{
    public function supports(Assert $assert): bool
    {
        return $assert instanceof Range;
    }

    /**
     * @param Range $assert
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

        $code = <<<'PHP'
if (%s >= %s && %s <= %s){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
        $message = $assert->message ?? 'The value must lie in the range from {{ min }} to {{ max }}';
        $message = preg_replace('/{{ min }}/', (string)$assert->min, $message);
        $message = preg_replace('/{{ max }}/', (string)$assert->max, $message);
        $code = sprintf(
            $code,
            $accessCode,
            $assert->min,
            $accessCode,
            $assert->max,
            $violationListVarName,
            $modelVar->type,
            $reflectionProperty->name,
            $message
        );
        return [$code];
    }
}
