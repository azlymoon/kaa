<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Collection;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class Container
{
    public function __construct(
        public ServiceCollection $services,
        public ParameterCollection $parameters,
    ) {
    }
}
