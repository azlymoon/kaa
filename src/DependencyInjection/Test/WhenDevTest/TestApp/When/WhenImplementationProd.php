<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenDevTest\TestApp\When;

use Kaa\DependencyInjection\Attribute\When;

#[When('prod')]
class WhenImplementationProd implements WhenParentInterface
{

}
