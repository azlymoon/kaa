<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp;



use Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp\When\WhenParentInterface;
use Kaa\DependencyInjection\Test\WhenUnknownTest\TestApp\WhenFactory\AbstractService;

readonly class SuperService
{
    public function __construct(
        public WhenParentInterface $whenService,
        public AbstractService $whenFactoryService,
    ) {
    }
}
