<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\When;

use Kaa\DependencyInjection\Attribute\When;

#[When(['dev', 'test'])]
class WhenImplementationDev implements WhenChildInterface
{

}
