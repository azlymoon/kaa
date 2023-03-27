<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

/**
 * Позволяет явно определить параметры сервиса
 */
#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Service
{
    public function __construct(
        public ?string $name = null,
        public bool $singleton = true,
    ) {
    }
}
