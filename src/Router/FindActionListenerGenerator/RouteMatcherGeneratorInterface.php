<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\CallableRoute;
use Kaa\Router\HttpRoute;

#[PhpOnly]
interface RouteMatcherGeneratorInterface
{
    /**
     * @param string $targetVarName
     * @param string $routeVarName
     * @param string $methodVarName
     * @param CallableRoute[] $routes
     * @param mixed[] $userConfig
     * @param ProvidedDependencies $providedDependencies
     * @return string
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
