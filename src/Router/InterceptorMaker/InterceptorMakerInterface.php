<?php

declare(strict_types=1);

namespace Kaa\Router\InterceptorMaker;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\Router\Action;
use Kaa\Router\CallableAction;

#[PhpOnly]
interface InterceptorMakerInterface
{
    /**
     * @param Action[] $actions
     * @param mixed[] $userConfig
     * @return CallableAction[]
     */
    public function makeInterceptors(
        array $actions,
        array $userConfig,
        ProvidedDependencies $providedDependencies
    ): array;
}
