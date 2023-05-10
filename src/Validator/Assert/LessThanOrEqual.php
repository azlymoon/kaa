<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class LessThanOrEqual extends Assert
{
    /**
     * @param int|float $value
     * @param string $message
     * @param string[] $allowTypes
     */
    public function __construct(
        public int|float $value,
        public string $message = 'This value should be less than or equal to {{ compared_value }}.',
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
