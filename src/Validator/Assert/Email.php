<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Email extends Assert
{
    public function __construct(
        public string $email,
        public string $mode = 'loose',
        public ?string $message = null,
    ) {
    }
}