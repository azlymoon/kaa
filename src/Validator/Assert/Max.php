<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Max extends Assert
{
    public function __construct(
        public float $value,
        public ?string $message = null,
    ) {
    }
}
