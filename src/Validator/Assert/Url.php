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
        public string $url,
        public array $protocols = ['http', 'https'],
        public bool $relativeProtocol = false,
        public ?string $message = null,
    ) {
    }
}
