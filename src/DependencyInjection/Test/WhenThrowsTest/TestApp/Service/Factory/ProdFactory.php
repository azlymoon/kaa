<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\Factory;

class ProdFactory
{
    public function __invoke(): ProdFactoryImpl
    {
        return new ProdFactoryImpl();
    }
}
