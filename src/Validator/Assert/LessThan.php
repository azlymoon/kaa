<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class LessThan extends Assert
{
    public function __construct(
        public int $value,
        public ?string $message = null,
    ) {
    }
}
