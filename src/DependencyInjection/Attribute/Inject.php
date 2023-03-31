<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

/**
 * Алиас сервиса или параметра, который должен быть injected
 */
#[PhpOnly]
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
readonly class Inject
{
    public function __construct(
        public string $serviceName,
    ) {
    }
}
