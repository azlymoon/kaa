<?php

namespace Kaa\CodeGen\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
interface NewInstanceGeneratorInterface
{
    /**
     * Возвращает код, который создаёт новый объект переданного класса или интерфейса
     */
    public function getNewInstanceCode(string $className): string;
}
