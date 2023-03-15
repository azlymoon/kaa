<?php

namespace Kaa\Validator\Assert;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[PhpOnly]
abstract readonly class Assert
{
    abstract public function supportsType(string $typeName): bool;

    abstract public function getAllowTypes(): array;
}
