<?php

declare(strict_types=1);

namespace Kaa\Router;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Attribute\Route;

#[PhpOnly]
readonly class CallableAction
{
    /**
     * @param Route[] $routes
     * @param Route[] $classRoutes
     */
    public function __construct(
        public array $routes,
        public array $classRoutes,
        public string $methodName,
        public string $varName,
        public string $newInstanceCode,
    ) {
    }
}
