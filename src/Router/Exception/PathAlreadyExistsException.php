<?php

declare(strict_types=1);

namespace Kaa\Router\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class PathAlreadyExistsException extends Exception
{
    public function __construct(
        string $message = 'Path with different name already exists!',
        int $code = 0
    ) {
        if (!empty($message)) {
            $this->message = sprintf('Path "%s" with different name already exists!', $message);
        }
        parent::__construct($this->message, $code);
    }
}
