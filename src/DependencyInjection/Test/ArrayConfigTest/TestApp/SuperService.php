<?php

declare(strict_types=1);

namespace Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp;



use Kaa\DependencyInjection\Attribute\Inject;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithClassDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\DynamicFactory\ServiceWithNamedDynamicFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\Named\NamedServiceInterface;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\StaticFactory\ServiceWithStaticFactory;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\When\WhenParentInterface;
use Kaa\DependencyInjection\Test\ArrayConfigTest\TestApp\WhenFactory\AbstractService;

readonly class SuperService
{
    public function __construct(
        public ServiceWithClassDynamicFactory $serviceWithClassDynamicFactory,
        public ServiceWithNamedDynamicFactory $serviceWithNamedDynamicFactory,

        public NamedServiceInterface $firstNamed,
        public NamedServiceInterface $secondNamed,

        public ServiceWithStaticFactory $serviceWithStaticFactory,

        public WhenParentInterface $whenService,

        public AbstractService $whenFactoryService,
    ) {
    }
}
