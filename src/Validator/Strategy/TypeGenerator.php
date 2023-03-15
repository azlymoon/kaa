<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Type;
use ReflectionException;
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
     * @throws ReflectionException
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
            $message = $assert->message ?? 'This value should be of type {{ type }}.';
            $message = preg_replace('/{{ type }}/', $type, $message);

            if ($reflectionProperty->getType()->allowsNull()) {
                if (isset(self::VALIDATION_FUNCTIONS[$type])) {
                    $code = <<<'PHP'
if (null !== %s && !%s(%s)){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
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
if (null !== %s && !(%s instanceof \%s)){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
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
            } else {
                if (isset(self::VALIDATION_FUNCTIONS[$type])) {
                    $code = <<<'PHP'
if (!%s(%s)){
    $%s[] = new \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
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
            }


            $resultCode[] = $code;
        }
        return $resultCode;
    }
}
