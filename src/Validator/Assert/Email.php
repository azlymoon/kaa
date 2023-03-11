<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Email extends Assert
{
    public function __construct(
        public string $mode = 'loose',
        public ?string $message = null,
        protected array $allowTypes = ['strings'],
    ) {
    }

    public function supportsType(string $typeName): bool {
        return (in_array($typeName, $this->allowTypes));
    }

    public function getAllowTypes(): array {
        return $this->allowTypes;
    }
}