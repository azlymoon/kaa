<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Range extends Assert
{
    /**
     * @param int|float $min
     * @param int|float $max
     * @param string $message
     * @param string[] $allowTypes
     */
    public function __construct(
        public int|float $min,
        public int|float $max,
        public string $message = 'The value must lie in the range from {{ min }} to {{ max }}',
        protected array $allowTypes = ['int', 'float'],
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
