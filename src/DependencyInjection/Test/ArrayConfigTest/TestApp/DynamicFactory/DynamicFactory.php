<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory;

class DynamicFactory
{
    public bool $wasCalled = false;

    public function create(): ServiceWithClassDynamicFactory
    {
        $this->wasCalled = true;

        return new ServiceWithClassDynamicFactory();
    }
}
