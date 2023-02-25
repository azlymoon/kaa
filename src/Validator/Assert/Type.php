<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Type extends Assert
{
    /**
     * @@param string|string[] $type
     */
    public function __construct(
        public string|array $type,
        public ?string $message = null,
    ) {
    }
}
