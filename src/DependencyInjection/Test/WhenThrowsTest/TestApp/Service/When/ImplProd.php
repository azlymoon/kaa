<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\When;

use Kaa\DependencyInjection\Attribute\When;

#[When('prod')]
class ImplProd implements WhenInterface
{

}
