<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\AttributeConfigTest\TestApp\Named;

use Kaa\DependencyInjection\Attribute\Service;

#[Service(name: 'first')]
class FirstNamedImplementation implements NamedServiceInterface
{

}
