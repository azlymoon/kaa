<?php

declare(strict_types=1);

namespace Kaa\Orm\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Entity implements Mapping
{
    public function __construct(
        public ?string $tableName = null,
    ) {
    }
}
