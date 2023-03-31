<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class AsEventListener
{
    public function __construct(
        public string $event,
        public string $dispatcher = 'kernel.dispatcher',
        public int $priority = 0,
        public string $method = '__invoke'
    ) {
    }
}
