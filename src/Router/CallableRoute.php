<?php

declare(strict_types=1);

namespace Kaa\Router;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class CallableRoute
{
    public function __construct(
        public string $path,
        public ?string $method,
        public string $name,
        public string $methodName,
        public string $varName,
        public string $newInstanceCode,
    ) {
    }
}
