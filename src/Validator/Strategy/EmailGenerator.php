<?php

declare(strict_types=1);

namespace Kaa\Validator\Strategy;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\InterceptorUtils\Exception\InaccessiblePropertyException;
use Kaa\InterceptorUtils\InterceptorUtils;
use Kaa\Router\Interceptor\AvailableVar;
use Kaa\Validator\Assert\Assert;
use Kaa\Validator\Assert\Email;
use Kaa\Validator\Exception\InvalidArgumentException;
use ReflectionProperty;

#[PhpOnly]
class EmailGenerator implements AssertGeneratorInterface
{
    private const PATTERN_HTML5_ALLOW_NO_TLD = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';
    private const PATTERN_HTML5 = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';
    private const PATTERN_LOOSE = '/^.+\@\S+\.\S+$/';

    private const EMAIL_PATTERNS = [
        'loose' => self::PATTERN_LOOSE,
        'html5' => self::PATTERN_HTML5,
        'html5-allow-no-tld' => self::PATTERN_HTML5_ALLOW_NO_TLD,
    ];

    public function supports(Assert $assert): bool
    {
        return $assert instanceof Email;
    }

    /**
     * @param Email $assert
     * @throws InaccessiblePropertyException
     * @throws InvalidArgumentException
     */
    public function generateAssert(
        Assert $assert,
        ReflectionProperty $reflectionProperty,
        AvailableVar $modelVar,
        string $violationListVarName
    ): array {
        $resultCode = [];
        $accessCode = InterceptorUtils::generateGetCode($reflectionProperty, $modelVar->name);

        if (!isset(self::EMAIL_PATTERNS[$assert->mode])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "%s::$mode" parameter value is not valid.',
                    $assert->mode,
                )
            );
        } else {
            $code = <<<PHP
if (!preg_match('%s', %s)){
    $%s[] = nnew \Kaa\Validator\Violation('%s', '%s', '%s');
}
PHP;
            $message = $assert->message ?? 'This value is not a valid email address.';
            $code  = sprintf(
                $code,
                self::EMAIL_PATTERNS[$assert->mode],
                $accessCode,
                $violationListVarName,
                $modelVar->type,
                $reflectionProperty->name,
                $message,
            );
            $resultCode[] = $code;
        }
        return $resultCode;
    }
}
