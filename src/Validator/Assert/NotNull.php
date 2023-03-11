<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class NotNull extends Assert
{
    public function __construct(
        public ?string $message = null,
    ) {
    }

    public function supportsType(string $typeName): bool {
        return true;
    }

    public function getAllowTypes(): array {
        return $this->allowTypes;
    }
}
