<?php

declare(strict_types=1);

namespace Kaa\InterceptorUtils\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class ParamNotFoundException extends Exception
{
}
