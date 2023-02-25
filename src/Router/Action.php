<?php

declare(strict_types=1);

namespace Kaa\Router;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\Router\Attribute\Route;
use Kaa\Router\Interceptor\Interceptor;
use ReflectionClass;
use ReflectionMethod;

#[PhpOnly]
readonly class Action
{
    /**
     * @param Route[] $routes
     * @param Interceptor[] $interceptors
     * @param Route[] $classRoutes
     * @param Interceptor[] $classInterceptors
     */
    public function __construct(
        public ReflectionMethod $reflectionMethod,
        public ReflectionClass $reflectionClass,
        public array $routes,
        public array $interceptors,
        public array $classRoutes,
        public array $classInterceptors,
    ) {
    }

    public function hasInterceptors(): bool
    {
        return !empty($this->interceptors) || !empty($this->classInterceptors);
    }
}
