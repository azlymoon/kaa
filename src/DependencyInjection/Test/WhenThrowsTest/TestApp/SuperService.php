<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp;

use Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\Factory\AbstractClass;
use Kaa\DependencyInjection\Test\WhenThrowsTest\TestApp\Service\When\WhenInterface;

readonly class SuperService
{
    public function __construct(
        public WhenInterface $whenService,
        public AbstractClass $whenFactoryService,
    ) {
    }
}
