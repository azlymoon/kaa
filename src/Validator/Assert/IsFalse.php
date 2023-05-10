<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class IsFalse extends Assert
{
    /**
     * @param string $message
     * @param string[] $allowTypes
     */
    public function __construct(
        public string $message = 'This value should be false.',
        protected array $allowTypes = ['bool'],
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
