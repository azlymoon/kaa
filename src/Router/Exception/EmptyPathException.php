<?php

declare(strict_types=1);

namespace Kaa\Router\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class EmptyPathException extends Exception{

    public function __construct(string $message = "Path can't be empty!", int $code = 0)
    {
        if (!($message == "")){
            $this->message = $message;
        }
        parent::__construct($this->message, $code);
    }
}