<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\DynamicFactory;

use Kaa\DependencyInjection\Attribute\Factory;

#[Factory(DynamicFactory::class, 'create')]
class ServiceWithClassDynamicFactory
{

}
