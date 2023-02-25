<?php

declare(strict_types=1);

namespace Kaa\Router\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
#[PhpOnly]
readonly class Route
{
    public function __construct(
        public string $route,
        public ?string $method = null,
        public ?string $name = null,
    ) {
    }
}
