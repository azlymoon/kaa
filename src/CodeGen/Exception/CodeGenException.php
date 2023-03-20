<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class CodeGenException extends Exception
{
}
