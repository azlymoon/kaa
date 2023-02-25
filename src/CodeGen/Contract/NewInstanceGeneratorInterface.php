<?php

namespace Kaa\CodeGen\Contract;

use Kaa\CodeGen\Attribute\PhpOnly;

#[PhpOnly]
interface NewInstanceGeneratorInterface
{
    /**
     * Возвращает код, который создаёт новый объект переданного класса или интерфейса
     * и присваивает его переданной переменной
     */
    public function getNewInstanceCode(string $varName, string $className): string;
}
