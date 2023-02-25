<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
enum InterceptorType
{
    case BEFORE;

    case AFTER;
}
