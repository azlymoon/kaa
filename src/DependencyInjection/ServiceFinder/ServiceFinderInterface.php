<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\ServiceFinder;

use Kaa\DependencyInjection\ServiceDefinition;

interface ServiceFinderInterface
{
    /**
     * @param mixed[] $userConfig
     * @return ServiceDefinition[]
     */
    public function findServices(array $userConfig): array;
}
