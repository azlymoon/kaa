<?php

declare(strict_types=1);

namespace Kaa\Validator\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class UnsupportedAssertException extends Exception
{
}
