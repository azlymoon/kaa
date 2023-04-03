<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Exception;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Exception\CodeGenException;

#[PhpOnly]
class EventDispatcherLinkerException extends CodeGenException
{
}
