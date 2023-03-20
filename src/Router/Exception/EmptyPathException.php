<?php

declare(strict_types=1);

namespace Kaa\Router\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class EmptyPathException extends Exception
{
    public function __construct(string $message = 'Path can not be empty!')
    {
        parent::__construct($message);
    }
}
