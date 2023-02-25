<?php

declare(strict_types=1);

namespace Kaa\ParamConverter\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class ParamConverterException extends Exception
{
}
