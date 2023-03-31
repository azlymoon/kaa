<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\When;

use Kaa\DependencyInjection\Attribute\When;

#[When('dev')]
class ImplDev implements WhenInterface
{

}
