<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Exception;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
class DependencyNotFoundException extends DependencyInjectionException
{
}
