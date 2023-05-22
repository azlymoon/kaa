<?php

namespace Kaa\CorsBundle;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Exception\EventDispatcherLinkerException;

#[PhpOnly]
class CorsGenerator implements GeneratorInterface
{
    private BootstrapProviderInterface $bootstrapProvider;
    public function __construct()
    {

    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void
    {
        $this->bootstrapProvider = $this->getBootstrapProvider($providedDependencies);
        $this->bootstrapProvider->addCode("var_dump(123);");
    }

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getBootstrapProvider(ProvidedDependencies $providedDependencies): BootstrapProviderInterface
    {
        if (!$providedDependencies->has(BootstrapProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                '%s requires %s to be provided',
                self::class,
                BootstrapProviderInterface::class,
            );
        }

        return $providedDependencies->get(BootstrapProviderInterface::class);
    }
}