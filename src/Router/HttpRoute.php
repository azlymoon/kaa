<?php

declare(strict_types=1);

namespace Kaa\Router;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class HttpRoute
{
    public function __construct(
        public string $path,
        public ?string $method,
        public string $name,
    ) {
    }
}
