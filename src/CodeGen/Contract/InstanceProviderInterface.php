<?php

namespace Kaa\CodeGen\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
interface InstanceProviderInterface
{
    /**
     * Возвращает код, который создаёт новый объект переданного класса или интерфейса
     */
    public function provideInstanceCode(string $className): string;
}
