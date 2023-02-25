<?php

namespace Kaa\CodeGen;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
interface GeneratorInterface
{
    /**
     * @param mixed[] $userConfig Дополнительная конфигурация пользователя
     * @param ProvidedDependencies $providedDependencies Доступные реализации зависимостей
     */
    public function generate(array $userConfig, ProvidedDependencies $providedDependencies): void;
}
