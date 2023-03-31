<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\WhenFactory;

class DevFactory
{
    public function __invoke(): DevService
    {
        return new DevService();
    }
}
