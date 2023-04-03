<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\Factory;

class DevFactory
{
    public function __invoke(): DevFactoryImpl
    {
        return new DevFactoryImpl();
    }
}
