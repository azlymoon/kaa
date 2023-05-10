<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Blank extends Assert
{
    /**
     * @param string $message
     * @param bool $allowNull
     * @param string[] $allowTypes
     */
    public function __construct(
        public string $message = 'This value should be blank.',
        public bool $allowNull = false,
        private array $allowTypes = ['string'],
    ) {
    }

    public function supportsType(string $typeName): bool
    {
        return (in_array($typeName, $this->allowTypes, true));
    }

    /**
     * @return string[]
     */
    public function getAllowTypes(): array
    {
        return $this->allowTypes;
    }
}
