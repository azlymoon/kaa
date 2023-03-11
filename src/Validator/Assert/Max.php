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
        protected array $allowTypes = ['int', 'float'],
    ) {
    }

    public function supportsType(string $typeName): bool {
        return (in_array($typeName, $this->allowTypes));
    }

    public function getAllowTypes(): array {
        return $this->allowTypes;
    }
}
