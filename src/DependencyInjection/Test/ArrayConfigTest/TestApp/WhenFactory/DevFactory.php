<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory;

class DevFactory
{
    public function __invoke(): DevService
    {
        return new DevService();
    }
}
