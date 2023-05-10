<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Email extends Assert
{
    /**
     * @param string $mode
     * @param string $message
     * @param string[] $allowTypes
     */
    public function __construct(
        public string $mode = 'loose',
        public string $message = 'This value is not a valid email address.',
        protected array $allowTypes = ['string'],
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
