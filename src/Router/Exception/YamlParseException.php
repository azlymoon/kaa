<?php

namespace Kaa\Router\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class YamlParseException extends Exception
{
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}