<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class NotNull extends Assert
{
    /**
     * @param string|null $message
     */
    public function __construct(
        public ?string $message = null,
    ) {
    }

    public function supportsType(string $typeName): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getAllowTypes(): array
    {
        return [];
    }
}
