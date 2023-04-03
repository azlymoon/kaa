<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\DynamicFactory;

use Kaa\DependencyInjection\Attribute\Service;

#[Service(name: 'factory.named')]
class NamedDynamicFactory
{
    public bool $wasCalled = false;

    public function __invoke(): ServiceWithNamedDynamicFactory
    {
        $this->wasCalled = true;

        return new ServiceWithNamedDynamicFactory();
    }
}
