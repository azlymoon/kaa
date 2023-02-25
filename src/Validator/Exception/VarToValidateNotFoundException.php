<?php

namespace Kaa\Validator\Exception;

use Exception;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class VarToValidateNotFoundException extends Exception
{
}
