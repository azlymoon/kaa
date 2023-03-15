<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class GreaterThanOrEqual extends Assert
{
    public function __construct(
        public int $value,
        public string|null $message = null,
        protected array $allowTypes = ['int', 'float'],
    ) {
    }

    public function supportsType(string $typeName): bool
    {
        return (in_array($typeName, $this->allowTypes));
    }

    public function getAllowTypes(): array
    {
        return $this->allowTypes;
    }
}
