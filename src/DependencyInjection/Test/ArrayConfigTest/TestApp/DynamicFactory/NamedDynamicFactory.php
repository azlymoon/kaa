<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory;

class NamedDynamicFactory
{
    public bool $wasCalled = false;

    public function __invoke(): ServiceWithNamedDynamicFactory
    {
        $this->wasCalled = true;

        return new ServiceWithNamedDynamicFactory();
    }
}
