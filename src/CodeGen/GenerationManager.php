<?php

namespace Kaa\CodeGen;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Config\ConfigInterface;
use Kaa\CodeGen\Contract\BasicBootstrapProvider;
use Kaa\CodeGen\Contract\BoostrapProviderInterface;
use Kaa\CodeGen\Exception\InvalidDependencyException;

#[PhpOnly]
readonly class GenerationManager
{
    public function __construct(
        private ConfigInterface $config,
    ) {
    }

    /**
     * Запускает кодогенерацию всех модулей в порядке, полученном из конфига.
     * Каждому следующему модулю передаёт зависимости, полученные из предыдущих.
     * Если два модуля предоставляют зависимость одинакового интерфейса,
     * то следующим модулям будет передана последняя из них
     *
     * @throws InvalidDependencyException
     */
    public function generate(): ProvidedDependencies
    {
        $providedDependencies = new ProvidedDependencies();
        $providedDependencies->add(
            BoostrapProviderInterface::class,
            new BasicBootstrapProvider($this->config->getUserConfig())
        );

        foreach ($this->config->getGenerators() as $generator) {
            $generator->generate($this->config->getUserConfig(), $providedDependencies);
        }

        foreach ($this->config->getGenerators() as $generator) {
            if ($generator instanceof DumpableInterface) {
                $generator->dump();
            }
        }

        foreach ($providedDependencies as $providedDependency) {
            if ($providedDependency instanceof DumpableInterface) {
                $providedDependency->dump();
            }
        }

        return $providedDependencies;
    }
}
