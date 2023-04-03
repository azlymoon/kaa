<?php

declare(strict_types=1);

namespace Kaa\CodeGen\Contract;

/**
 * Класс, реализующий этот интерфейс, создаёт код, который должен быть выполнен при получении каждого запроса
 */
interface BootstrapProviderInterface
{
    public function addCode(string $code): void;

    public function getCallBootstrapCode(): string;
}
