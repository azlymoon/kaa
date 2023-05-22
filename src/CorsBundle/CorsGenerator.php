<?php

namespace Kaa\CorsBundle;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Contract\BootstrapProviderInterface;
use Kaa\CodeGen\Contract\InstanceProviderInterface;
use Kaa\CodeGen\Exception\CodeGenException;
use Kaa\CodeGen\Exception\NoDependencyException;
use Kaa\CodeGen\GeneratorInterface;
use Kaa\CodeGen\ProvidedDependencies;
use Kaa\DependencyInjection\Contract\InstanceProvider;
use Kaa\DependencyInjection\DependencyInjectionGenerator;
use Kaa\DependencyInjection\Exception\EventDispatcherLinkerException;

#[PhpOnly]
class CorsGenerator implements GeneratorInterface
{
    private const ADD_LISTENER_CODE = <<<'PHP'
%dispatcher%->addListener('%eventName%', %callable%, %priority%);
PHP;

    private InstanceProvider $instanceProvider;
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
        $this->instanceProvider = $this->getInstanceProvider($providedDependencies);
        $replacements = [
            '%dispatcher%' => $this->instanceProvider->provideInstanceCode('kernel.dispatcher'),
            '%eventName%' => 'http.kernel.find.action',
            '%callable%' => "[ \Kaa|CorsBundle\PvpenderCorsBundle::class, 'checkOptions']",
            '%priority%' => 0
        ];
        $this->bootstrapProvider->addCode(strtr(self::ADD_LISTENER_CODE, $replacements));
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

    /**
     * @throws NoDependencyException
     * @throws CodeGenException
     */
    private function getInstanceProvider(ProvidedDependencies $providedDependencies): InstanceProvider
    {
        if (!$providedDependencies->has(InstanceProviderInterface::class)) {
            EventDispatcherLinkerException::throw(
                'You must execute %s before executing %s',
                DependencyInjectionGenerator::class,
                self::class
            );
        }

        $instanceProvider = $providedDependencies->get(InstanceProviderInterface::class);
        if (!$instanceProvider instanceof InstanceProvider) {
            EventDispatcherLinkerException::throw(
                'There is implementation %s of %s in provided dependencies, '
                . 'but %s is compatible only with %s provided by %s',
                $instanceProvider::class,
                InstanceProviderInterface::class,
                self::class,
                InstanceProvider::class,
                DependencyInjectionGenerator::class
            );
        }

        return $instanceProvider;
    }
}