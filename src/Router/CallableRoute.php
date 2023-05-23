<?php

declare(strict_types=1);

namespace Kaa\Router;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
readonly class CallableRoute
{
    public string $method;

    public function __construct(
        public string $path,
        ?string $method,
        public string $name,
        public string $methodName,
        public string $varName,
        public string $newInstanceCode,
    ) {
        $this->method = ($method === null) ? 'GET' : $method;
    }
}
