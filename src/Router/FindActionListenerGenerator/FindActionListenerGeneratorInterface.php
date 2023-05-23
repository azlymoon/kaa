<?php

declare(strict_types=1);

namespace Kaa\Router\FindActionListenerGenerator;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\CallableRoute;

#[PhpOnly]
interface FindActionListenerGeneratorInterface
{
    /**
     * @param CallableRoute[] $callableRoutes
     * @param RouteMatcherGeneratorInterface $routeMatcherGenerator
     * @param mixed[] $userConfig
     * @param ProvidedDependencies $providedDependencies
     */
    public function generate(
        array $callableRoutes,
        RouteMatcherGeneratorInterface $routeMatcherGenerator,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): void;
}
