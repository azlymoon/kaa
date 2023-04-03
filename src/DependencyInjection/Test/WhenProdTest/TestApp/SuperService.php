<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\WhenProdTest\TestApp;



use Kaa\DependencyInjection\Test\WhenProdTest\TestApp\When\WhenParentInterface;
use Kaa\DependencyInjection\Test\WhenProdTest\TestApp\WhenFactory\AbstractService;

readonly class SuperService
{
    public function __construct(
        public WhenParentInterface $whenService,
        public AbstractService $whenFactoryService,
    ) {
    }
}
