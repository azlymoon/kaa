<?php

namespace Kaa\CodeGen;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\Config\ConfigInterface;

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
     */
    public function generate(): ProvidedDependencies
    {
        $providedDependencies = new ProvidedDependencies();

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
