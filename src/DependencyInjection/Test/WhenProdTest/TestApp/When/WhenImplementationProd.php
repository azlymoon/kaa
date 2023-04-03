<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenProdTest\TestApp\When;

use Kaa\DependencyInjection\Attribute\When;

#[When('prod')]
class WhenImplementationProd implements WhenParentInterface
{

}
