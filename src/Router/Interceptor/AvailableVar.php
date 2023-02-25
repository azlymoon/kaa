<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class AvailableVar
{
    public function __construct(
        public string $name,
        public string $type,
    ) {
    }
}
