<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
#[PhpOnly]
readonly class Interceptor
{
    public function __construct(
        public InterceptorGeneratorInterface $generator,
        public InterceptorType $type,
    ) {
    }
}
