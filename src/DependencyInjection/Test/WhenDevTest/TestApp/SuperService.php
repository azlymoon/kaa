<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenDevTest\TestApp;



use Kaa\DependencyInjection\Test\WhenDevTest\TestApp\When\WhenParentInterface;
use Kaa\DependencyInjection\Test\WhenDevTest\TestApp\WhenFactory\AbstractService;

readonly class SuperService
{
    public function __construct(
        public WhenParentInterface $whenService,
        public AbstractService $whenFactoryService,
    ) {
    }
}
