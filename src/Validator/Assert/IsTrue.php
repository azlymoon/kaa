<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class IsTrue extends Assert
{
    public function __construct(
        public ?string $message = null,
    ) {
    }
}
