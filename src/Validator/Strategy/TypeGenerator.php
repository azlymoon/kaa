<?php

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Type;
use ReflectionProperty;

#[PhpOnly]
class TypeGenerator implements AssertGeneratorInterface
{
    private const VALIDATION_FUNCTIONS = [
        'bool' => 'is_bool',
        'boolean' => 'is_bool',
        'int' => 'is_int',
        'integer' => 'is_int',
        'long' => 'is_int',
        'float' => 'is_float',
        'double' => 'is_float',
        'real' => 'is_float',
        'numeric' => 'is_numeric',
        'string' => 'is_string',
        'scalar' => 'is_scalar',
        'array' => 'is_array',
        'iterable' => 'is_iterable',
        'countable' => 'is_countable',
        'callable' => 'is_callable',
        'object' => 'is_object',
        'resource' => 'is_resource',
        'null' => 'is_null',
        'alnum' => 'ctype_alnum',
        'alpha' => 'ctype_alpha',
        'cntrl' => 'ctype_cntrl',
        'digit' => 'ctype_digit',
        'graph' => 'ctype_graph',
        'lower' => 'ctype_lower',
        'print' => 'ctype_print',
        'punct' => 'ctype_punct',
        'space' => 'ctype_space',
        'upper' => 'ctype_upper',
        'xdigit' => 'ctype_xdigit',
    ];

    public function supports(Assert $assert): bool
    {
        return $assert instanceof Type;
    }

    /**
     * @param Type $assert
     * @throws InaccessiblePropertyException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        $resultCode = [];
        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $modelVar->name);

        $types = (array)$assert->type;
        foreach ($types as $type) {
            $type = strtolower($type);
            if (isset(self::VALIDATION_FUNCTIONS[$type])) {
                $code = <<<'PHP'
if (!%s(%s)){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
                $message = $assert->message ?? 'Default message';
                $code = sprintf(
                    $code,
                    self::VALIDATION_FUNCTIONS[$type],
                    $accessCode,
                    $violationListVarName,
                    $modelVar->type,
                    $reflectionProperty->name,
                    $message,
                );
            } else {
                $code = <<<'PHP'
if (!(%s instanceof \%s)){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;

                $message = $assert->message ?? 'Default message';
                $code = sprintf(
                    $code,
                    $accessCode,
                    $type,
                    $violationListVarName,
                    $modelVar->type,
                    $reflectionProperty->name,
                    $message,
                );
            }
            $resultCode[] = $code;
        }
        return $resultCode;
    }
}
