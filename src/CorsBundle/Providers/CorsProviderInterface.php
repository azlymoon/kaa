<?php

namespace Kaa\CorsBundle\Providers;

/**
 * Класс, реализующий этот интерфейс, создаёт код, который должен быть выполнен при получении каждого запроса
 */
interface CorsProviderInterface
{
    public function addCode(string $code): void;
}
