<?php

declare(strict_types=1);

namespace Kaa\Router\Interceptor;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Action;

#[PhpOnly]
interface InterceptorGeneratorInterface
{
    /**
     * @param AvailableVars $availableVars
     * @param Action $action
     * @param mixed[] $userConfig
     * @param ProvidedDependencies $providedDependencies
     * @return string
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string;
}
