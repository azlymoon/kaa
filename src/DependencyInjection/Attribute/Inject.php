<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class Inject
{
    public function __construct(
        public string $service
    ) {
    }
}
