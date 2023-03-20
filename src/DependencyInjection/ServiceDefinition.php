<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class ServiceDefinition
{
    /**
     * @param string[] $aliases
     * @param string[] $dependencies
     */
    public function __construct(
        public string $type,
        public array $aliases = [],
        public array $dependencies = [],
    ) {
    }
}
