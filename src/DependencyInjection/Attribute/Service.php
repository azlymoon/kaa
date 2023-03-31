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
    /**
     * @param array<string, mixed> $tags
     */
    public function __construct(
        public ?string $name = null,
        public array $tags = [],
        public bool $singleton = true,
    ) {
    }
}
