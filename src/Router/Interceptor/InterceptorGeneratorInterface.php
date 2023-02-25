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
     * @param mixed[] $userConfig
     */
    public function generate(
        AvailableVars $availableVars,
        Action $action,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): string;
}
