<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenProdTest\TestApp\WhenFactory;

use Kaa\DependencyInjection\Attribute\Factory;

#[Factory(ProdFactory::class, when: 'prod')]
#[Factory(DevFactory::class, when: 'dev')]
#[Factory(DefaultFactory::class)]
abstract class AbstractService
{

}
