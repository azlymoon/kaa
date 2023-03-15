<?php

declare(strict_types=1);

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
readonly class Url extends Assert
{
    public function __construct(
        public array $protocols = ['http', 'https'],
        public bool $relativeProtocol = false,
        public string|null $message = null,
        protected array $allowTypes = ['string'],
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
