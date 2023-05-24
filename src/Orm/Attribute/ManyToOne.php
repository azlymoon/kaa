<?php

declare(strict_types=1);

namespace Kaa\Orm\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ManyToOne implements Mapping
{
    public function __construct(
        public string $targetEntity,
        public ?string $name = null
    ) {
    }
}
