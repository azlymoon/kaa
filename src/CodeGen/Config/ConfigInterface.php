<?php

namespace Kaa\CodeGen\Config;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\CodeGen\GeneratorInterface;

#[PhpOnly]
interface ConfigInterface
{
    /**
     * Возвращает список генераторов в том порядке, в котором они должны быть вызваны
     * @return GeneratorInterface[]
     */
    public function getGenerators(): array;

    /**
     * Возвращает дополнительную пользовательскую конфигурацию
     * @return mixed[]
     */
    public function getUserConfig(): array;
}
