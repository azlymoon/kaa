<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class CodeGenException extends Exception
{
    final public function __construct(string $message = "")
    {
        parent::__construct($message);
    }

    /**
     * Кидает исключение, передавая первый аргумент как шаблон для sprintf, а остальные как параметры
     *
     * @throws static
     */
    public static function throw(string $text, string ...$params): never
    {
        throw new static(
            sprintf(
                $text,
                ...$params,
            )
        );
    }
}
