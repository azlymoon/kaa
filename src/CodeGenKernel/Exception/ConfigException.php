<?php

declare(strict_types=1);

namespace Kaa\CodeGenKernel\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class ConfigException extends Exception
{
}
