<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class Service
{
    /**
     * @param string[]|string $aliases
     */
    public function __construct(
        public array|string $aliases = [],
    ) {
    }
}
