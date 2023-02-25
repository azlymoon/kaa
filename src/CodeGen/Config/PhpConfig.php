<?php

namespace Kaa\CodeGen\Config;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\GeneratorInterface;

#[PhpOnly]
final readonly class PhpConfig implements ConfigInterface
{
    /**
     * @param GeneratorInterface[] $generators Генераторы в том порядке, в котором они должны быть вызваны
     * @param mixed[] $userConfig Дополнительная конфигурация, которая будет передана генераторам
     */
    public function __construct(
        private array $generators,
        private array $userConfig,
    ) {
    }

    public function getGenerators(): array
    {
        return $this->generators;
    }

    public function getUserConfig(): array
    {
        return $this->userConfig;
    }
}
