<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Attribute;

use Attribute;
use Kaa\CodeGen\Attribute\PhpOnly;

/**
 * Сервис, помеченный этим аттрибутом,
 * будет injected, только если в runtime config указан такой же app-env как в параметре
 * (или такой же, как один из тех, что в параметре)
 */
#[PhpOnly]
#[Attribute(Attribute::TARGET_CLASS)]
readonly class When
{
    public const DEFAULT_ENVIRONMENT = 'DEFAULT ENVIRONMENT';

    /**
     * @param string[]|string $environment
     */
    public function __construct(
        public array|string $environment
    ) {
    }
}
