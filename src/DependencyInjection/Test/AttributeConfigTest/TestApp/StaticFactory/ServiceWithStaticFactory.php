<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\StaticFactory;

use Kaa\DependencyInjection\Attribute\Factory;

#[Factory(StaticFactory::class, 'create', isStatic: true)]
class ServiceWithStaticFactory
{

}
