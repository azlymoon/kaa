<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Blank extends Assert
{
    public function __construct(
        public string|null $message = null,
        public bool $allowNull = false,
        private array $allowTypes = ['string'],
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
