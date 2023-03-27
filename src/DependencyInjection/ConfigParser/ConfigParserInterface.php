<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\ConfigParser;

use Kaa\CodeGen\Attribute\PhpOnly;
use Kaa\DependencyInjection\Collection\Container;
use Kaa\DependencyInjection\Collection\ServiceCollection;

#[PhpOnly]
interface ConfigParserInterface
{
    /**
     * Изменяет ServiceCollection в соответствии с конфигом
     * Парсит константные параметры
     *
     * @param mixed[] $userConfig
     */
    public function parseConfig(array $userConfig, ServiceCollection $services): Container;
}
