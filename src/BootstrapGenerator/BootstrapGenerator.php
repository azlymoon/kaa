<?php

declare(strict_types=1);

namespace Kaa\BootstrapGenerator;

use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Exception\InvalidDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;

class BootstrapGenerator implements GeneratorInterface
{
    /**
     * @throws InvalidDependencyException
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $providedDependencies->add(BootstrapProviderInterface::class, new BasicBootstrapProvider($userConfig));
    }
}
