<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\HttpRoute;

#[PhpOnly]
interface RouteMatcherGeneratorInterface
{
    /**
     * @param HttpRoute[] $routes
     * @param mixed[] $userConfig
     */
    public function generateMatchCode(
        string $targetVarName,
        string $routeVarName,
        string $methodVarName,
        array $routes,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string;
}
