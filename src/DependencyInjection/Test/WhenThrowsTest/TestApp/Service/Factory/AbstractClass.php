<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\Factory;

use Kaa\DependencyInjection\Attribute\Factory;

#[Factory(ProdFactory::class, when: 'prod')]
#[Factory(DevFactory::class, when: 'dev')]
abstract class AbstractClass
{

}
