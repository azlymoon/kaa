<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\DynamicFactory;

use Kaa\DependencyInjection\Attribute\Factory;

#[Factory('factory.named')]
class ServiceWithNamedDynamicFactory
{

}
