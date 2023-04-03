<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Contract;

readonly class ServiceInfo
{
    /**
     * @param mixed[] $tags
     */
    public function __construct(
        public string $class,
        public array $tags = [],
    ) {
    }
}
